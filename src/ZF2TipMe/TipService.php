<?php
/**
 * ZF2TipMe
 *
 * @link      http://github.com/cgmartin/ZF2TipMe for the canonical source repository
 * @copyright Copyright (c) 2013 Christopher Martin (http://cgmartin.com)
 * @license   New BSD License https://raw.github.com/cgmartin/ZF2TipMe/master/LICENSE
 */

namespace ZF2TipMe;

use ZfcBase\EventManager\EventProvider;
use Zend\Form\Form;
use Zend\Log\Logger;
use Zend\Mail;
use Zend\Mail\Transport\TransportInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Debug\Debug;

class TipService extends EventProvider implements ServiceManagerAwareInterface
{
    /** @var array */
    protected $options;

    /** @var Logger */
    protected $logger;

    /** @var TransportInterface */
    protected $mailTransport;

    /** @var ServiceManager */
    protected $serviceManager;

    /**
     * @param  array $data TipForm data
     * @return array
     * @throws \Stripe_Error
     */
    public function sendPayment($data)
    {
        $tipMeCfg = $this->getOptions();
        // set your secret key: remember to change this to your live secret key in production
        // see your keys here https://manage.stripe.com/account
        \Stripe::setApiKey($tipMeCfg['stripe_secret_key']);

        $description = 'Gift ' . $data['tipOption'];
        if (!empty($data['email'])) {
            $description .= ' from ' . $data['email'];
        }
        $tipItem       = $tipMeCfg['tip_options'][$data['tipOption']];
        $amountInCents = intval($tipItem['amount'] * 100);

        $chargeData = new \ArrayObject(array(
            "amount"      => $amountInCents, // amount in cents
            "currency"    => "usd",
            "card"        => $data['stripeToken'], // Payment details submitted from form
            "description" => $description
        ));

        $this->getEventManager()->trigger(__FUNCTION__, $this, array(
            'formData' => $data, 'chargeData' => $chargeData
        ));
        try {
            // create the charge on Stripe's servers - this will charge the user's card
            $charge = \Stripe_Charge::create((array)$chargeData);
        } catch(\Stripe_Error $e) { // All stripe related errors
            $body = $e->getJsonBody();
            $this->getLogger()->crit('Charge failure: ' . print_r($body, 1));
            throw $e;
        }
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array(
            'formData' => $data, 'chargeData' => $chargeData, 'charge' => $charge
        ));

        $this->getLogger()->debug(
            $description . " [$" . sprintf('%.2f', $tipItem['amount']) . "] " . $charge['id']
        );

        $this->sendEmailNotification($data, $charge);

        return $charge;
    }

    protected function sendEmailNotification($data, $charge)
    {
        $tipMeCfg = $this->getOptions();
        $tipItem  = $tipMeCfg['tip_options'][$data['tipOption']];
        $renderer = $this->getServiceManager()->get('ViewRenderer');

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

    public function getOptions()
    {
        if (!$this->options) {
            $config = $this->getServiceManager()->get('config');
            $this->options = $config['zf2tipme'];
        }
        return $this->options;
    }

    public function setOptions($config)
    {
        $this->options = $config;
        return $this;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = $this->getServiceManager()->get('zf2tipme_logger');
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
            $this->mailTransport = $this->getServiceManager()->get('zf2tipme_mailtransport');
        }
        return $this->mailTransport;
    }

    public function setMailTransport(TransportInterface $transport)
    {
        $this->mailTransport = $transport;
        return $this;
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}
