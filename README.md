
# Crunchmail-client-php 0.4

Official PHP wrapper for Crunchmail API

[![Build Status](https://travis-ci.org/crunchmail/crunchmail-client-php.svg?branch=master)](https://travis-ci.org/crunchmail/crunchmail-client-php)
[![Latest Stable Version](https://poser.pugx.org/crunchmail/crunchmail-client-php/v/stable)](https://packagist.org/packages/crunchmail/crunchmail-client-php)
[![Latest Unstable Version](https://poser.pugx.org/crunchmail/crunchmail-client-php/v/unstable)](https://packagist.org/packages/crunchmail/crunchmail-client-php)


# Important Notice

This API is **under development**. Do not use in production!


# Install

## Using composer (recommended)

Crunchmail-client uses [composer](https://getcomposer.org/).

First [install composer](https://getcomposer.org/doc/00-intro.md) if needed,
then install crunchmail-php-client into your project directory:

    composer require crunchmail/crunchmail-client-php

After installing, you need to require Composer's autoloader:

```php
    require 'vendor/autoload.php';
```

## Manually

If you wish to install the client manually, you will need to first install
guzzle 6 and load the libraries yourself, as composer will not handle the
autoload for you in that case.


# Getting started

See [example script](./examples/index.php) to see how to use the API.


# Documentation

* [Crunchmail PHP Client documentation](http://crunchmail-api-php-client.readthedocs.org/en/latest/)
* [Developer documentation](http://crunchmail.github.io/crunchmail-client-php)


# Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md).
