
# Crunchmail-client-php

Official PHP wrapper for Crunchmail API


# Important Notice

This API is **under development**. Do not use in production!


# Install

Crunchmail-client uses [composer](https://getcomposer.org/).

First edit your composer.json config file:

    {
        "require": {
            "guzzlehttp/guzzle": "^6.1",
            "crunchmail/crunchmail-client-php"
        }
    }

[Install composer](https://getcomposer.org/doc/00-intro.md) if needed.

Then install:

    ./composer.phar self-update
    ./composer.phar install


# Getting started

    <?php

    // require libs
    require 'vendor/autoload.php';

    // prepare configuration
    $config = [
          'base_uri'    => 'https://api.crunchmail.me/v1/',
          'client_uri'  => '/customers/999999999/',
          // SSL
          'verify'      => true,
          'auth'        => [ 'api', 'my-super-secret-apikey' ]
    ];

    // instanciate new client
    $Client = new Crunchmail\Client($config);

