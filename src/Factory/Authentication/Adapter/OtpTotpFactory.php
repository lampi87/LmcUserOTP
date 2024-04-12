<?php

declare(strict_types=1);

namespace LmcUserOtp\Factory\Authentication\Adapter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use LmcUserOtp\Authentication\Adapter\OtpTotp;

class OtpTotpFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $db = new OtpTotp();
        if ($serviceLocator instanceof ServiceManager) {
            $db->setServiceManager($serviceLocator);
        }
        return $db;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->__invoke($serviceLocator, '');
    }
}
