<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'ZF2TipMe\TipController' => 'ZF2TipMe\TipController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'tip-me' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/tip-me',
                    'defaults' => array(
                        'controller'    => 'ZF2TipMe\TipController',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // Success route
                    'success' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/success',
                            'defaults' => array(
                                'controller'    => 'ZF2TipMe\TipController',
                                'action'        => 'success',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'zf2tipme' => __DIR__ . '/../view',
        ),
    ),
);
