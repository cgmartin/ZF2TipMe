<?php
/**
 * ZF2TipMe
 *
 * @link      http://github.com/cgmartin/ZF2TipMe for the canonical source repository
 * @copyright Copyright (c) 2013 Christopher Martin (http://cgmartin.com)
 * @license   New BSD License https://raw.github.com/cgmartin/ZF2TipMe/master/LICENSE
 */

namespace ZF2TipMe;

use Zend\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

class TipForm extends Form
{
    /** @var array */
    protected $tipMeConfig = array();

    /**
     * @param  null|int|string  $name        Optional name for the element
     * @param  array            $tipMeConfig Optional Tip Me configuration
     * @param  array            $options     Optional options for the element
     */
    public function __construct($name = null, $tipMeConfig = array(), $options = array())
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'POST');
        $this->tipMeConfig = $tipMeConfig;
        $this->addElements();
        $this->addInputFilter();
    }

    public function getStripePublishKey()
    {
        return $this->tipMeConfig['stripe_publish_key'];
    }

    public function getTipOptions()
    {
        return $this->tipMeConfig['tip_options'];
    }

    public function getRecipientName()
    {
        return $this->tipMeConfig['recipient_name'];
    }

    public function getConfirmMessage()
    {
        return $this->tipMeConfig['confirm_message'];
    }

    public function isTestMode()
    {
        return $this->tipMeConfig['test_mode'];
    }

    protected function addElements()
    {
        $tipOptions = $this->getTipOptions();

        // Tip Options
        $valueOptions = array();
        $firstTipKey  = null;
        foreach ($tipOptions as $key => $tipOption) {
            $firstTipKey = ($firstTipKey) ?: $key;
            $amount = sprintf('%.2f', $tipOption['amount']);
            $valueOptions[$key] = array(
                'value'      => $key,
                'label'      =>  '$' . $amount . ' - ' . $tipOption['title'],
                'attributes' => array(
                    'data-tip-amount' => $amount,
                    'data-tip-image'  => $tipOption['img_src'],
                ),
            );
        }
        $tipOptionRadio = new Element\Radio('tipOption');
        $tipOptionRadio
            ->setAttribute('id', 'tipOption')
            ->setAttribute('required', true)
            ->setValueOptions($valueOptions)
            ->setValue($firstTipKey);
        $this->add($tipOptionRadio);

        // Email
        $email = new Element\Text('email');
        $email->setAttribute('id', 'email');
        $this->add($email);

        // Comments
        $message = new Element\Textarea('message');
        $message
            ->setAttribute('id', 'message')
            ->setAttribute('maxlength', 200);
        $this->add($message);

        // Hidden token
        $stripeToken = new Element\Hidden('stripeToken');
        $stripeToken->setAttribute('id', 'stripeToken');
        $this->add($stripeToken);
    }

    protected function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();

        // Tip Options
        $tipOptionInput = $this->get('tipOption')->getInputSpecification();
        $tipOptionInput['required'] = true;
        $inputFilter->add($tipOptionInput);

        // Email
        $emailInput = new InputFilter\Input('email');
        $emailInput->setRequired(false);
        $emailInput->getFilterChain()
            ->attachByName('stringtrim');
        $inputFilter->add($emailInput);

        // Comments
        $messageInput = new InputFilter\Input('message');
        $messageInput->setRequired(false);
        $messageInput->getFilterChain()
            ->attachByName('stripnewlines')
            ->attachByName('striptags')
            ->attachByName('stringtrim')
            ->attach(new SubstrFilter(
                $this->get('message')->getAttribute('maxlength')
            ));
        $inputFilter->add($messageInput);

        // Hidden token
        $stripeTokenInput = new InputFilter\Input('stripeToken');
        $stripeTokenInput->setRequired(true);
        $inputFilter->add($stripeTokenInput);

        $this->setInputFilter($inputFilter);
    }
}