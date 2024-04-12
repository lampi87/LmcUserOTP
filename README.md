# Usage

Define `auth_adapters` in lmcuser.global.php
```
    'auth_adapters' => [
        80 => 'LmcUser\Authentication\Adapter\Db',
        10 => 'LmcUserOtp\Authentication\Adapter\OtpSms',
        0 => 'LmcUserOtp\Authentication\Adapter\OtpTotp'
    ],
```

# Additional config

Define in `local.php`config file. SMS config is based on SMS Eagle API
```
    'sms-gw' => [
        'host' => '',
        'token' => '',
        'otp-text' => 'OTP SMS text: %s'
    ],
```
