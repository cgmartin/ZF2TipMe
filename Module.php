<?php
/**
 * ZF2TipMe
 *
 * @link      http://github.com/cgmartin/ZF2TipMe for the canonical source repository
 * @copyright Copyright (c) 2013 Christopher Martin (http://cgmartin.com)
 * @license   New BSD License https://raw.github.com/cgmartin/ZF2TipMe/master/LICENSE
 */

namespace ZF2TipMe;

use Zend\EventManager\EventInterface as Event;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;

class Module implements AutoloaderProviderInterface
{
    public function init(ModuleManager $moduleManager)
    {
        // Remember to keep the init() method as lightweight as possible
        //$events = $moduleManager->getEventManager();
        //$events->attach('loadModules.post', array($this, 'modulesLoaded'));
    }

    /**
     * Called when all modules are loaded (must attach event in init)
     */
    public function modulesLoaded(Event $e)
    {
        $moduleManager = $e->getTarget();
        $loadedModules = $moduleManager->getLoadedModules();
        $config        = $moduleManager->getConfig();
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
                array(
                    'Stripe' => './vendor/stripe-php/lib/Stripe/Stripe.php',
                ),
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		            // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
                'prefixes' => array(
                    'Stripe' => './vendor/stripe-php/lib/Stripe',
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your application
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'zf2tipme_tipform' => function($sm) {
                    $config = $sm->get('config');
                    return new TipForm('tipForm', $config['zf2tipme']);
                },
            ),
        );
    }
}
