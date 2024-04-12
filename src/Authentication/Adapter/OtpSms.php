<?php

declare(strict_types=1);

namespace LmcUserOtp\Authentication\Adapter;

use GBFAbstract\Entity\AbstractUserRole;
use Laminas\Authentication\Result as AuthenticationResult;
use Laminas\EventManager\EventInterface;
use Laminas\Http\Response;
use Laminas\Ldap\Exception\LdapException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Session\Container as SessionContainer;
use LmcUser\Authentication\Adapter\AbstractAdapter;
use LmcUser\Authentication\Adapter\AdapterChainEvent;
use LmcUser\Mapper\UserInterface as UserMapperInterface;
use LmcUser\Options\ModuleOptions;
use LmcUser\Module;

/**
 * @psalm-suppress MissingConstructor
 */
class OtpSms extends AbstractOtpAdapter
{
    const MAX_OTP_REQUESTS = 3;
    const OTP_TRY_TIMEOUT_MIN = 5;

    /**
     *
     * @var UserMapperInterface|null
     */
    protected $mapper;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @var ModuleOptions|null
     */
    protected $options;

    /**
     * Called when user id logged out
     *
     * @param AdapterChainEvent $e
     */
    public function logout(AdapterChainEvent $e): void
    {
        $this->getStorage()->clear();
    }

    /**
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->getServiceManager()->get('config');
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getObjectManager()
    {
        return $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
    }

    /**
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @param AdapterChainEvent $e
     * @return bool|Response
     */
    public function authenticate(AdapterChainEvent $e)
    {
        $storage = $this->getStorage()->read();
        if (!$this->isSatisfied()) {
            $e->setCode(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND)
                ->setMessages(array('A record with the supplied identity could not be found.'));
            return false;
        }

        /** @var \LmcUserOtp\Entity\AbstractUserOtp|null $userObject */
        $userObject = $this->getMapper()->findById($storage['identity']);
        if (!$userObject) {
            $e->setCode(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND)
                ->setMessages(array('A record with the supplied identity could not be found.'));
            $this->setSatisfied(false);
            return false;
        }

        if ($this->isOtpSatisfied() || false === $userObject->getUseOtp()) {
            $storage = $this->getStorage()->read();
            $e->setIdentity($storage['identity'])
                ->setCode(AuthenticationResult::SUCCESS)
                ->setMessages(array('Authentication successful.'));
            return true;
        }
        /** @var \Laminas\Http\Request $request */
        $request = $e->getRequest();
        $code = $request->getPost()->get('code');
        if (!$code) {
            /** @var \DateTime|null $tryTimeout */
            $tryTimeout = $userObject->getOtpTryTimeout('object');
            if (!\is_null($tryTimeout)) {
                if ($tryTimeout >= new \DateTime()) {
                    $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
                        ->setMessages(array('Too many OTP requests'));
                    $this->setSatisfied(false);
                    return false;
                } else {
                    $userObject->setOtpTryTimeout(null);
                }
            }
            $tryCount = $userObject->getOtpTryCount();
            $tryCount++;
            if ($tryCount > self::MAX_OTP_REQUESTS) {
                $tryTimeout = new \DateTime();
                $tryTimeout->modify('+' . self::OTP_TRY_TIMEOUT_MIN . ' minutes');
                $userObject->setOtpTryTimeout($tryTimeout);
                $userObject->setOtpTryCount(0);
                $this->getMapper()->update($userObject);
                $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
                    ->setMessages(array('Too many OTP requests'));
                $this->setSatisfied(false);
                return false;
            }
            $randCode = rand(100000, 999999);
            $userObject->setOtpTryCount($tryCount);
            $userObject->setOtp(\strval($randCode));
            $userObject->setOtpTimeout(\time() + 60 * 5);
            $this->sendSms($userObject);
            $this->getMapper()->update($userObject);
            $router  = $this->serviceManager->get('Router');
            $url = $router->assemble([], [
                'name' => 'lmcuser/otp'
            ]);
            $response = new Response();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(302);
            return $response;
        }

        if ($userObject->getOtp() != $code) {
            $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
                ->setMessages(array('Supplied credential is invalid.'));
            $this->setOtpSatisfied(false);
            return false;
        }

        if ($userObject->getOtpTimeout('unix') < \time()) {
            $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
                ->setMessages(array('Supplied credential is invalid.'));
            $this->setOtpSatisfied(false);
            return false;
        }

        // regen the id
        $session = new SessionContainer(Module::LMC_USER_SESSION_STORAGE_NAMESPACE);
        $session->getManager()->regenerateId();

        // Success!
        $userObject->setOtpTryTimeout(null);
        $userObject->setOtpTryCount(0);
        $this->getMapper()->update($userObject);
        $e->setIdentity($userObject->getId());
        // Update user's password hash if the cost parameter has changed
        $storage = $this->getStorage()->read();
        $storage['is_otp_satisfied'] = true;
        $storage['identity'] = $e->getIdentity();
        $this->getStorage()->write($storage);
        $e->setCode(AuthenticationResult::SUCCESS)
            ->setMessages(array('Authentication successful.'));
        return true;
    }

    /**
     *
     * @param \LmcUserOtp\Entity\AbstractUserOtp $user
     */
    private function sendSms($user): void
    {
        $config = $this->getConfig();
        if (isset($config['sms-gw']['host']) === false || isset($config['sms-gw']['token']) === false) {
            return;
        }
        $code = $user->getOtp();
        if (\is_null($code)) {
            return;
        }
        $smsText = _('Your otp code: %s');
        if (isset($config['core']['app']['otp-text']) && empty($config['core']['app']['otp-text']) === false) {
            $smsText = $config['core']['app']['otp-text'];
        }
        $translator = $this->getServiceManager()->get('translator');
        $message = \urlencode(\sprintf($translator->translate($smsText), $code));
        $url = 'https://' . $config['sms-gw']['host'] . '/http_api/send_sms?access_token=' . $config['sms-gw']['token'] . '&to=' . $user->getMobile() . '&message=' . $message;
        $xml = \file_get_contents($url);
    }

    /**
     * getMapper
     *
     * @return UserMapperInterface
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->mapper = $this->getServiceManager()->get('lmcuser_user_mapper');
        }
        return $this->mapper;
    }

    /**
     * setMapper
     *
     * @param UserMapperInterface $mapper
     * @return $this
     */
    public function setMapper(UserMapperInterface $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager($serviceManager): void
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     *
     * @param ModuleOptions $options
     */
    public function setOptions(ModuleOptions $options): void
    {
        $this->options = $options;
    }

    /**
     *
     * @return ModuleOptions
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = $this->getServiceManager()->get('lmcuser_module_options');
        }
        return $this->options;
    }
}
