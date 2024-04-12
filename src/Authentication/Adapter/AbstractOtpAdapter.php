<?php

declare(strict_types=1);

namespace LmcUserOtp\Authentication\Adapter;

use Laminas\Authentication\Storage;
use LmcUser\Module;
use LmcUser\Authentication\Adapter\AbstractAdapter;

/**
 * Class AbstractAdapter
 */
abstract class AbstractOtpAdapter extends AbstractAdapter
{
    /**
     * Check if this adapter is satisfied or not
     *
     * @return bool
     */
    public function isOtpSatisfied()
    {
        $storage = $this->getStorage()->read();
        return (isset($storage['is_otp_satisfied']) && true === $storage['is_otp_satisfied']);
    }

    /**
     * Set if this adapter is satisfied or not
     *
     * @param  bool $bool
     * @return AbstractOtpAdapter
     */
    public function setOtpSatisfied($bool = true)
    {
        $storage = $this->getStorage()->read() ?: [];
        $storage['is_otp_satisfied'] = $bool;
        $this->getStorage()->write($storage);
        return $this;
    }
}
