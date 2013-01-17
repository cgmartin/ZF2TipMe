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

    'zf2tipme' => array(
        'test_mode'            => false,
        'error_log'            => './data/log/tipme.log',
        'recipient_name'       => 'RECIPIENT',
        'admin_email'          => 'admin@email.address',
        'statement_descriptor' => 'STATEMENT_DESCRIPTOR',
        'tip_options' => array(
            'coffee' => array(
                'title'   => 'Cup of Starbucks coffee (12 oz)',
                'amount'  => 2.50,
                'img_src' => 'http://placehold.it/200x150',
            ),
            'redbull' => array(
                'title'  => 'Red Bull (20 oz, sugar free)',
                'amount' => 4.48,
                'img_src' => 'http://placehold.it/200x150',
            ),
            'music' => array(
                'title'  => 'MP3 music (album)',
                'amount' => 9.99,
                'img_src' => 'http://placehold.it/200x150',
            ),
        ),
        'mail_transport_options' => array(
            'path' => './data/mail/',
        ),
    ),
);
