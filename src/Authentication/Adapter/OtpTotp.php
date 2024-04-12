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
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

/**
 * @psalm-suppress MissingConstructor
 */
class OtpTotp extends AbstractOtpAdapter
{
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
            $router  = $this->serviceManager->get('Router');
            $url = $router->assemble([], [
                'name' => 'lmcuser/otp'
            ]);
            $response = new Response();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(302);
            return $response;
        }

        $secret = $userObject->getTotpSecret();
        if (\is_null($secret)) {
            $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
                ->setMessages(array('Supplied credential is invalid.'));
            $this->setOtpSatisfied(false);
            return false;
        }

        $totpAuth = new GoogleAuthenticator();
        if (!$totpAuth->checkCode($secret, $code)) {
            $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
                ->setMessages(array('Supplied credential is invalid.'));
            $this->setOtpSatisfied(false);
            return false;
        }

        // regen the id
        $session = new SessionContainer(Module::LMC_USER_SESSION_STORAGE_NAMESPACE);
        $session->getManager()->regenerateId();

        // Success!
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
