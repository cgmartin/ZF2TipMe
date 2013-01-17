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
use Zend\Log\Logger;
use Zend\Session\Container;
use Zend\Mail;
use Zend\Mail\Transport\TransportInterface;
use Zend\Debug\Debug;

class TipController extends AbstractActionController
{
    /** @var Container */
    protected $sessionContainer;

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

                try {
                    $this->sendPayment($data);

                    $this->getSessionContainer()->successfulTip = array(
                        'email'     => $data['email'],
                        'message'   => $data['message'],
                        'tipOption' => $data['tipOption'],
                    );

                    $response = $this->redirect()->toRoute('tip-me/success');
                    return $response->setStatusCode(303);

                } catch(\Stripe_Error $e) { // All stripe related errors
                    $body = $e->getJsonBody();
                    $form->setMessages(array(
                        'stripeToken' => array($body['error']['type'] => $body['error']['message'])
                    ));
                }
            }
        }

        return array('form' => $form);
    }

    /**
     * Page displayed upon a successful payment, after indexAction()
     */
    public function successAction()
    {
        $data = $this->getSessionContainer()->successfulTip;
        if (empty($data)) {
            return $response = $this->redirect()->toRoute('tip-me');
        }

        $tipMeCfg = $this->getTipMeConfig();
        $tipItem  = $tipMeCfg['tip_options'][$data['tipOption']];
        return array(
            'data'                => $data,
            'tipItem'             => $tipItem,
            'adminEmail'          => $tipMeCfg['admin_email'],
            'statementDescriptor' => $tipMeCfg['statement_descriptor'],
        );
    }

    public function getSessionContainer()
    {
        if (!$this->sessionContainer) {
            $this->sessionContainer = new Container('tipMe');
        }
        return $this->sessionContainer;
    }

    public function setSessionContainer($container)
    {
        $this->sessionContainer = $container;
        return $this;
    }

    // TODO: Move below into a service

    /** @var array */
    protected $tipMeConfig;

    /** @var TipForm */
    protected $tipForm;

    /** @var Logger */
    protected $logger;

    /** @var TransportInterface */
    protected $mailTransport;

    protected function sendPayment($data)
    {
        $tipMeCfg = $this->getTipMeConfig();
        // set your secret key: remember to change this to your live secret key in production
        // see your keys here https://manage.stripe.com/account
        \Stripe::setApiKey($tipMeCfg['stripe_secret_key']);

        $description = 'Gift ' . $data['tipOption'];
        if (!empty($data['email'])) {
            $description .= ' from ' . $data['email'];
        }
        $tipItem       = $tipMeCfg['tip_options'][$data['tipOption']];
        $amountInCents = intval($tipItem['amount'] * 100);

        try {
            // create the charge on Stripe's servers - this will charge the user's card
            $charge = \Stripe_Charge::create(array(
                "amount"      => $amountInCents, // amount in cents
                "currency"    => "usd",
                "card"        => $data['stripeToken'], // Payment details submitted from form
                "description" => $description)
            );
        } catch(\Stripe_Error $e) { // All stripe related errors
            $body = $e->getJsonBody();
            $this->getLogger()->crit('Charge failure: ' . print_r($body, 1));
            throw $e;
        }

        $this->getLogger()->debug(
            $description . " [$" . sprintf('%.2f', $tipItem['amount']) . "]"
        );

        $this->sendEmailNotification($data, $charge);

        return $charge;
    }

    protected function sendEmailNotification($data, $charge)
    {
        $tipMeCfg = $this->getTipMeConfig();
        $tipItem  = $tipMeCfg['tip_options'][$data['tipOption']];
        $renderer = $this->getServiceLocator()->get('ViewRenderer');

        $content = $renderer->render(
            'zf2-tip-me/email/email-notify-text', array(
                'data'    => $data,
                'tipItem' => $tipItem,
                'charge'  => $charge,
            )
        );
        $message = new Mail\Message();
        $message->setBody($content);
        $message->setFrom($tipMeCfg['admin_email'], $tipMeCfg['recipient_name']);
        $message->addTo($tipMeCfg['admin_email'],   $tipMeCfg['recipient_name']);
        $message->setSubject('[TipMe] New gift! ' . $tipItem['title']);

        try {
            $this->getMailTransport()->send($message);
        } catch (\Exception $ex) {
            $this->getLogger()->err($ex->getMessage());
        }
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

    /**
     * @return TipForm
     */
    public function getTipForm()
    {
        if (!$this->tipForm) {
            $this->tipForm = $this->getServiceLocator()->get('zf2tipme_tipform');
        }
        return $this->tipForm;
    }

    public function setTipForm(TipForm $form)
    {
        $this->tipForm = $form;
        return $this;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = $this->getServiceLocator()->get('zf2tipme_logger');
        }
        return $this->logger;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return TransportInterface
     */
    public function getMailTransport()
    {
        if (!$this->mailTransport) {
            $this->mailTransport = $this->getServiceLocator()->get('zf2tipme_mailtransport');
        }
        return $this->mailTransport;
    }

    public function setMailTransport(TransportInterface $transport)
    {
        $this->mailTransport = $transport;
        return $this;
    }

}
