<?php
/**
 * ZF2TipMe
 *
 * @link      http://github.com/cgmartin/ZF2TipMe for the canonical source repository
 * @copyright Copyright (c) 2013 Christopher Martin (http://cgmartin.com)
 * @license   New BSD License https://raw.github.com/cgmartin/ZF2TipMe/master/LICENSE
 */

namespace ZF2TipMe;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Form;

class TipController extends AbstractActionController
{
    /**
     * Primary tip page
     */
    public function indexAction()
    {
        $form  = $this->getTipForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();
                $this->sendPayment($data);

                $response = $this->redirect()->toRoute('tip-me/success');
                $response->setStatusCode(303);
            }
        }

        return array('form' => $form);
    }

    /**
     * Page displayed upon a successful payment, after indexAction()
     */
    public function successAction()
    {
        return array();
    }

    // TODO: Move below into a service

    /** @var array */
    protected $tipMeConfig;

    /** @var Form */
    protected $tipForm;

    protected function sendPayment($data)
    {
        $tipMeCfg = $this->getTipMeConfig();

        // set your secret key: remember to change this to your live secret key in production
        // see your keys here https://manage.stripe.com/account
        \Stripe::setApiKey($tipMeCfg['stripe_secret_key']);

        // create the charge on Stripe's servers - this will charge the user's card
        $charge = \Stripe_Charge::create(array(
                "amount"      => 1000, // amount in cents, again
                "currency"    => "usd",
                "card"        => $data['stripeToken'], // Payment details submitted from form
                "description" => "zf2-tip-me@example.com")
        );

        return $charge;
    }

    public function getTipMeConfig()
    {
        if (!$this->tipMeConfig) {
            $config = $this->getServiceLocator()->get('config');
            $this->tipMeConfig = $config['zf2tipme'];
        }
        return $this->tipMeConfig;
    }

    public function setTipMeConfig($config)
    {
        $this->tipMeConfig = $config;
        return $this;
    }

    public function getTipForm()
    {
        if (!$this->tipForm) {
            $this->tipForm = $this->getServiceLocator()->get('zf2tipme_tipform');
        }
        return $this->tipForm;
    }

    public function setTipForm(Form $form)
    {
        $this->tipForm = $form;
        return $this;
    }

}
