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
            'ZF2TipMe' => __DIR__ . '/../view',
        ),
        'template_map' => array(
            'zf2-tip-me/partial/tip-form'        => __DIR__ . '/../view/zf2-tip-me/partial/tip-form.phtml',
            'zf2-tip-me/partial/test-data-btn'   => __DIR__ . '/../view/zf2-tip-me/partial/test-data-btn.phtml',
            'zf2-tip-me/email/email-notify-text' => __DIR__ . '/../view/zf2-tip-me/email/email-notify-text.phtml',
        ),
    ),
    'zf2tipme' => array(
        'test_mode'       => false,
        'error_log'       => './data/log/tipme.log',
        'mail_dir'       => './data/mail/',
        'recipient_name'  => 'RECIPIENT',
        'refund_email'    => 'refunds@email.address',
        'confirm_message' => 'Send a gift of ${{amount}} from your {{cardType}} card to {{recipient}}?' .
                             '\nPressing "OK" will process the charge.',
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
    ),
);
