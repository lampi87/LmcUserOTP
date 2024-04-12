<?php

namespace LmcUserOtp\Controller;

use Laminas\Form\FormInterface;
use LmcUser\Controller\UserController;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\ResponseInterface as Response;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use LmcUser\Service\User as UserService;
use LmcUser\Options\UserControllerOptionsInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class UserOtpController extends UserController
{
    const ROUTE_OTP          = 'lmcuser/otp';

    /**
     * @var FormInterface|null
     */
    protected $otpForm;

    /**
     *
     * @return array|Response
     */
    public function otpAction()
    {
        if ($this->lmcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }

        $request = $this->getRequest();
        $form    = $this->getOtpForm();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if (!$request->isPost()) {
            return array(
                'otpForm' => $form,
                'redirect'  => $redirect
            );
        }

        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->setNamespace('lmcuser-login-form')->addMessage($this->failedLoginMessage);
            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_LOGIN) . ($redirect ? '?redirect=' . rawurlencode($redirect) : ''));
        }

        return $this->forward()->dispatch(static::CONTROLLER_NAME, array('action' => 'authenticate'));
    }

    /**
     * @return FormInterface
     */
    public function getOtpForm()
    {
        if (!$this->otpForm) {
            $this->setOtpForm($this->serviceLocator->get('lmcuser_otp_form'));
        }
        return $this->otpForm;
    }

    /**
     *
     * @param FormInterface $otpForm
     * @return $this
     */
    public function setOtpForm(FormInterface $otpForm)
    {
        $this->otpForm = $otpForm;
        return $this;
    }
}
