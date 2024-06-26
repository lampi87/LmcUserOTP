<?php

namespace LmcUserOtp\Form;

use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use LmcUser\Options\AuthenticationOptionsInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Otp extends ProvidesEventsForm
{
    /**
     * @var AuthenticationOptionsInterface
     */
    protected $authOptions;

    public function __construct(?string $name, AuthenticationOptionsInterface $options)
    {
        $this->setAuthenticationOptions($options);

        parent::__construct($name);

        $this->add(
            array(
                'name' => 'code',
                'type' => 'tel',
                'options' => array(
                    'label' => 'Code',
                ),
                'attributes' => array(
                    'type' => 'tel'
                ),
            )
        );
        if ($this->getAuthenticationOptions()->getUseLoginFormCsrf()) {
            $this->add([
                'type' => '\Laminas\Form\Element\Csrf',
                'name' => 'security',
                'options' => [
                    'csrf_options' => [
                        'timeout' => $this->getAuthenticationOptions()->getLoginFormTimeout()
                    ]
                ]
            ]);
        }
        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel('Sign In')
            ->setAttributes(
                array(
                    'type'  => 'submit',
                )
            );

        $this->add(
            $submitElement,
            array(
                'priority' => -100,
            )
        );
    }

    /**
     * Set Authentication-related Options
     *
     * @param  AuthenticationOptionsInterface $authOptions
     * @return Otp
     */
    public function setAuthenticationOptions(AuthenticationOptionsInterface $authOptions)
    {
        $this->authOptions = $authOptions;

        return $this;
    }

    /**
     * Get Authentication-related Options
     *
     * @return AuthenticationOptionsInterface
     */
    public function getAuthenticationOptions()
    {
        return $this->authOptions;
    }
}
