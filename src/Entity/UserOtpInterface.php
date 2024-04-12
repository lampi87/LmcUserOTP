<?php

namespace LmcUserOtp\Entity;

use LmcUser\Entity\UserInterface;

interface UserOtpInterface extends UserInterface
{
    /**
     * Get otp usage.
     *
     * @return bool
     */
    public function getUseOtp(): bool;

    /**
     * Set otp usage.
     *
     * @param  bool $useOtp
     * @return UserOtpInterface
     */
    public function setUseOtp(bool $useOtp);

    /**
     * Get otp.
     *
     * @return string|null
     */
    public function getOtp(): ?string;

    /**
     * Set otp.
     *
     * @param  string|null $otp
     * @return UserOtpInterface
     */
    public function setOtp(?string $otp);

    /**
     * Get otp timeout.
     *
     * @return mixed|null
     */
    public function getOtpTimeout();

    /**
     * Set otp timeout.
     *
     * @param  mixed|null $otpTimeout
     * @return UserOtpInterface
     */
    public function setOtpTimeout($otpTimeout);

    /**
     * Get totp secret.
     *
     * @return string|null
     */
    public function getTotpSecret(): ?string;

    /**
     * Set totp secret.
     *
     * @param  string|null $totpSecret
     * @return UserOtpInterface
     */
    public function setTotpSecret(?string $totpSecret);

    /**
     * Get otp try count.
     *
     * @return int
     */
    public function getOtpTryCount(): int;

    /**
     * Set otp try count.
     *
     * @param  int $otpTryCount
     * @return UserOtpInterface
     */
    public function setOtpTryCount(int $otpTryCount);

    /**
     * Get otp try timeout.
     *
     * @return mixed|null
     */
    public function getOtpTryTimeout();

    /**
     * Set otp try timeout.
     *
     * @param  mixed|null $otpTryTimeout
     * @return UserOtpInterface
     */
    public function setOtpTryTimeout($otpTryTimeout);
}
