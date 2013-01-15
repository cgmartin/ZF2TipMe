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
use ZfcBase\Form\ProvidesEventsForm;

class TipForm extends ProvidesEventsForm
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

    protected function addElements()
    {
        $tipOptions = $this->getTipOptions();

        // Tip Options
        $valueOptions = array();
        foreach ($tipOptions as $key => $tipOption) {
            $amount = sprintf('%.2f', $tipOption['amount']);
            $valueOptions[$key] = array(
                'value'      => $key,
                'label'      =>  '$' . $amount . ' - ' . $tipOption['title'],
                'attributes' => array('data-amount' => $amount),
            );
        }
        $tipOptionRadio = new Element\Radio('tipOption');
        $tipOptionRadio
            ->setAttribute('id', 'tipOption')
            ->setValueOptions($valueOptions);
        $this->add($tipOptionRadio);

        // Email
        $email = new Element\Email('email');
        $email->setAttribute('id', 'email');
        $this->add($email);

        // Comments
        $message = new Element\Textarea('message');
        $message->setAttribute('id', 'message');
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
            ->attachByName('stringtrim');
        $inputFilter->add($messageInput);

        // Hidden token
        $stripeTokenInput = new InputFilter\Input('stripeToken');
        $stripeTokenInput->setRequired(true);
        $inputFilter->add($stripeTokenInput);

        $this->setInputFilter($inputFilter);
    }
}