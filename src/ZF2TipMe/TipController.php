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
use Zend\Debug\Debug;

class TipController extends AbstractActionController
{
    /** @var Container */
    protected $sessionContainer;

    /** @var TipService */
    protected $tipService;

    /** @var TipForm */
    protected $tipForm;

    /**
     * Primary tip page
     */
    public function indexAction()
    {
        $tipService = $this->getTipService();
        $form       = $this->getTipForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();

                try {
                    $charge = $tipService->sendPayment($data);

                    $this->getSessionContainer()->successfulTip = array(
                        'email'     => $data['email'],
                        'message'   => $data['message'],
                        'tipOption' => $data['tipOption'],
                        'chargeId'  => $charge['id'],
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

        $tipService = $this->getTipService();
        $tipMeCfg   = $tipService->getOptions();
        $tipItem    = $tipMeCfg['tip_options'][$data['tipOption']];

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('data' => $data, 'tipItem' => $tipItem));

        return array(
            'data'                => $data,
            'tipItem'             => $tipItem,
            'adminEmail'          => $tipMeCfg['admin_email'],
            'statementDescriptor' => $tipMeCfg['statement_descriptor'],
        );
    }

    /**
     * @return Container
     */
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

    /**
     * @return TipService
     */
    public function getTipService()
    {
        if (!$this->tipService) {
            $this->tipService = $this->getServiceLocator()->get('ZF2TipMe\TipService');
        }
        return $this->tipService;
    }

    public function setUserService(TipService $tipService)
    {
        $this->tipService = $tipService;
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
}
