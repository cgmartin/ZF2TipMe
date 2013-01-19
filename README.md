ZF2TipMe
========
Version 1.0.0 / Created by Christopher Martin /
[BSD-3-Clause](https://github.com/cgmartin/ZF2TipMe/blob/master/LICENSE.txt) License


Introduction
------------
Accept donations directly on your site with this ZF2 module
and a [free Stripe account](https://stripe.com).

![ZF2TipMe example screenshot](https://www.evernote.com/shard/s47/sh/45f862ca-d884-49e9-ba86-8b0f3cac3c6d/b15e9b433830be159e43c54a8152ad35/res/647413ea-e58a-4ecf-89c9-26fe22527729/skitch.png?resizeSmall&width=832)

Features
--------
* Notification emails can be sent after successful payments.
* Test mode toggle, with a list of test credit cards for various testing scenarios.
* Designed for easy customization. Donation items are configurable. Event hooks available.
* Failed transactions are logged.
* No PCI data is sent to or stored on your server, thanks to Stripe.

**Stripe currently is only available in United States and Canada.**

Get notified of when Stripe is [available in your country](https://stripe.com/global).


Installation
------------

### Composer / Packagist Install

Add `"minimum-stability": "dev"` to your composer.json file, and run:

```
# From project's base directory...
% composer.phar require cgm/zf2-tip-me
Please provide a version constraint for the cgm/config-admin requirement: dev-master
```

### Enable the ZF2TipMe Module

Edit your `application.config.php` and enable the `ZfcBase` and `ZF2TipMe` modules:
```php
return array(
    'modules' => array(
        // ...
        'ZfcBase',
        'ZF2TipMe',
    ),
    // ...
);
```

### Create Directories and Symbolic Links

Out of the box, this module is configured to write log files to
`./data/log` and `./data/mail`. The mail logs are used when you have
chosen not to use a mail server.

```
# From project's base directory...
% cd data
% mkdir log mail
% chmod 777 log mail
# Or set the appropriate group permissions to be writable by the webserver
```

There is an asset folder with CSS and JavaScript files.
You can link them to `./public/tip-me-assets`, or potentially
use a asset management module:

```
# From the project's base dir...
% cd public
% ln -s ../vendor/cgm/zf2-tip-me/public tip-me-assets
```

### Edit the Configuration

Copy the .global and .local dist files from the module config directory:

```
# From project's base directory...
% cd config/autoload
% cp ../../vendor/cgm/zf2-tip-me/config/zf2tipme.global.php.dist ./zf2tipme.global.php
% cp ../../vendor/cgm/zf2-tip-me/config/zf2tipme.local.php.dist ./zf2tipme.local.php
```

Edit the `zf2tipme.global.php` file to taste:
```php
<?php
return array(
    'zf2tipme' => array(
        'error_log'            => './data/log/tipme.log',
        'recipient_name'       => '{{{RECIPIENT}}}',            // Your name
        'admin_email'          => '{{{admin@email.address}}}',  // Displayed for refunds, and used for mail notifications
        'statement_descriptor' => '{{{STATEMENT_DESCRIPTOR}}}', // Stripe account setting
        'tip_options' => array(                                 // Customize away...
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
        'mail_transport_options' => array(                       // Used with default 'zf2tipme_mailtransport' factory in Module.php
            'path' => './data/mail/',
        ),
    ),
);
```

Edit the `zf2tipme.local.php` file with your Stripe API keys:

```php
<?php
$testMode = true;
return array(
    'zf2tipme' => array(
        'test_mode'          => $testMode,
        'stripe_secret_key'  => ($testMode)
                              ? '{{{TEST_SECRET_KEY_HERE}}}'
                              : '{{{LIVE_SECRET_KEY_HERE}}}',
        'stripe_publish_key' => ($testMode)
                              ? '{{{TEST_PUBLISHABLE_KEY_HERE}}}'
                              : '{{{LIVE_PUBLISHABLE_KEY_HERE}}}',
    ),
);
```

### Verify it works

Point your browser to `/tip-me` and use the "Fill Test Data" dropdown
for testable credit cards.


Like this module?
-----------------
[Tip me a coffee](https://zf2-cgm.rhcloud.com/tip-me) :coffee: ;)

