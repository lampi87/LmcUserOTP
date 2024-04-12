<?php

declare(strict_types=1);

namespace LmcUserOtp\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GBFAbstract\Entity\AbstractUser;

/**
 *
 * @ORM\MappedSuperclass
 */
class AbstractUserOtp extends AbstractUser implements UserOtpInterface
{
    /**
     * +----------------------------------------------------------+ *
     * | class constants | *
     * +----------------------------------------------------------+
     */

    /**
     * +----------------------------------------------------------+ *
     * | functions | *
     * +----------------------------------------------------------+
     */

    /**
     * +----------------------------------------------------------+ *
     * | constructor | *
     * +----------------------------------------------------------+
     */

    public function __construct()
    {
        parent::__construct();
        $this->useOtp = false;
        $this->mobile = null;
        $this->otp = null;
        $this->otpTimeout = null;
        $this->totpSecret = null;
        $this->otpTryCount = 0;
        $this->otpTryTimeout = null;
    }

    /**
     * +----------------------------------------------------------+ *
     * | mappings | *
     * +----------------------------------------------------------+
     */

    /**
     * +----------------------------------------------------------+ *
     * | properties | *
     * +----------------------------------------------------------+
     */

    /**
     *
     * @ORM\Column(type="boolean",options={"default":0})
     * @var boolean $useOtp
     */
    protected bool $useOtp;

    /**
     *
     * @ORM\Column(type="string",length=20,nullable=true)
     * @var string|null $mobile
     */
    protected ?string $mobile;

    /**
     *
     * @ORM\Column(type="string",length=10,nullable=true)
     * @var string|null $otp
     */
    protected ?string $otp;

    /**
     *
     * @ORM\Column(type="datetime",nullable=true)
     * @var \DateTime|NULL $otpTimeout
     */
    protected ?\DateTime $otpTimeout;

    /**
     *
     * @ORM\Column(type="string",length=50,nullable=true)
     * @var string|null $totpSecret
     */
    protected ?string $totpSecret;

    /**
     *
     * @ORM\Column(type="integer")
     * @var int $otpTryCount
     */
    protected int $otpTryCount;

    /**
     *
     * @ORM\Column(type="datetime",nullable=true)
     * @var \DateTime|NULL $otpTryTimeout
     */
    protected ?\DateTime $otpTryTimeout;

    /**
     * +----------------------------------------------------------+ *
     * | getter & setter | *
     * +----------------------------------------------------------+
     */

    /**
     *
     * @return boolean
     */
    public function getUseOtp(): bool
    {
        return $this->useOtp;
    }

    /**
     *
     * @param boolean $useOtp
     * @return $this
     */
    public function setUseOtp(bool $useOtp)
    {
        $this->useOtp = $useOtp;
        return $this;
    }

    /**
     *
     * @param string $format
     * @return \DateTime|string|int|null
     */
    public function getOtpTimeout($format = "date")
    {
        if (is_null($this->otpTimeout)) {
            return $this->otpTimeout;
        }
        return $this->convertDateFormat($this->otpTimeout, $format);
    }

    /**
     *
     * @param \DateTime|string|int|null $otpTimeout
     * @return $this
     */
    public function setOtpTimeout($otpTimeout)
    {
        if (is_null($otpTimeout)) {
            $this->otpTimeout = $otpTimeout;
        } else {
            $this->otpTimeout = $this->convertDateValue($otpTimeout);
        }
        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getOtp(): ?string
    {
        return $this->otp;
    }

    /**
     *
     * @param string|null $otp
     * @return $this
     */
    public function setOtp(?string $otp)
    {
        $this->otp = $otp;
        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     *
     * @param string|null $mobile
     * @return $this
     */
    public function setMobile(?string $mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    /**
     *
     * @param string|null $totpSecret
     * @return $this
     */
    public function setTotpSecret(?string $totpSecret)
    {
        $this->totpSecret = $totpSecret;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getOtpTryCount(): int
    {
        return $this->otpTryCount;
    }

    /**
     *
     * @param int $otpTryCount
     * @return $this
     */
    public function setOtpTryCount(int $otpTryCount)
    {
        $this->otpTryCount = $otpTryCount;
        return $this;
    }

    /**
     *
     * @param string $format
     * @return \DateTime|string|int|null
     */
    public function getOtpTryTimeout($format = "object")
    {
        if (\is_null($this->otpTryTimeout)) {
            return $this->otpTryTimeout;
        }
        return $this->convertDateFormat($this->otpTryTimeout, $format);
    }

    /**
     *
     * @param \DateTime|string|int|null $otpTryTimeout
     * @return $this
     */
    public function setOtpTryTimeout($otpTryTimeout)
    {
        if (\is_null($otpTryTimeout)) {
            $this->otpTryTimeout = $otpTryTimeout;
        } else {
            $this->otpTryTimeout = $this->convertDateValue($otpTryTimeout);
        }
        return $this;
    }
}
