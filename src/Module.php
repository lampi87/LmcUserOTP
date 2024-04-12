<?php

declare(strict_types=1);

namespace LmcUserOtp;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;

class Module implements ConfigProviderInterface, ServiceProviderInterface
{
    /**
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     *
     * @return array
     */
    public function getControllerConfig()
    {
        return [
            'factories' => [
                'lmcuserotp' => Factory\Controller\UserOtpControllerFactory::class,
            ],
        ];
    }

    /**
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                'lmcuser_otp_form' => Factory\Form\Otp::class,
                Authentication\Adapter\OtpSms::class => Factory\Authentication\Adapter\OtpSmsFactory::class,
                Authentication\Adapter\OtpTotp::class => Factory\Authentication\Adapter\OtpTotpFactory::class
            ]
        ];
    }
}
