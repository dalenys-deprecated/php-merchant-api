[Dalenys Merchant API] (http://developer.dalenys.com/)

A simple PHP implementation of the Dalenys payment platform API.

[![Build Status](https://travis-ci.org/dalenys/php-merchant-api.svg?branch=master)](https://travis-ci.org/dalenys/php-merchant-api)
[![Latest Stable Version](https://poser.pugx.org/dalenys/php-merchant-api/v/stable)](https://packagist.org/packages/dalenys/php-merchant-api) 
[![Total Downloads](https://poser.pugx.org/dalenys/php-merchant-api/downloads)](https://packagist.org/packages/dalenys/php-merchant-api) 
[![License](https://poser.pugx.org/dalenys/php-merchant-api/license)](https://packagist.org/packages/dalenys/php-merchant-api)

## Installing

### Composer
You can easily install this library by adding the following lines to your composer.json file

```json
{
  "require": {
    "dalenys/php-merchant-api": "2.*"
  }
}
```

or by using this command line in a terminal at the root of your project

```bash
composer require dalenys/php-merchant-api 1.*
```

### Manual install
You can install this library manually by simply cloning it to your project and including scripts/autoload.php


## Using
 
### Building a simple payment form

Here is the code sample for implementing a simple 10€ payment form

```php
<?php

define('DALENYS_IDENTIFIER', 'YOUR ACCOUNT IDENTIFIER');
define('DALENYS_PASSWORD', 'YOUR ACCOUNT PASSWORD');

// Just implement DALENYS_IDENTIFIER and DALENYS_PASSWORD as defined
$dalenys = Dalenys_Api_ClientBuilder::buildProductionFormClient(DALENYS_IDENTIFIER, DALENYS_PASSWORD);

echo $dalenys->buildPaymentFormButton(10000, 'order_123', 'user_123456', 'Payment sample');
```

### Payment options
You can specify some additional options to the buildPaymentFormButton method.
The most useful options are:
- CREATEALIAS = yes/no => Ask for the creation of a rebilling alias (allowing one click payments or subscription like payments)
- 3DSECURE = yes/no => Ask for 3DSECURE authentication
- CARDFULLNAME => When set the card holder inputs will be filled with specified data

For the full list of options you can read the Dalenys documentation

### Sandbox environment
You can easily test your integration with the sandbox environment. This environment will simulate payments without processing any real money move.
You just have to use another builder method:

```php
<?php

$dalenys = Dalenys_Api_ClientBuilder::buildSandboxFormClient(DALENYS_IDENTIFIER, DALENYS_PASSWORD);
```

### Transaction edition
You can edit a transaction: capturing or refunding an authorization.
You should use the direct link AP:

```php
<?php

$dalenys = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient(DALENYS_IDENTIFIER, DALENYS_PASSWORD);

$dalenys->capture('A1234', 'order_42', 'capturing a transaction');
```

## Testing

- First you have to copy tests/ftests/config.php.dist to tests/ftests/config.php
- Then run `composer tests`

If you want to run only the unit or functional test suites:

`composer utests` or `composer ftests`

## Documentation

- The documentation is available in [doc](doc) folder.
    - Ensure to download the [phar package](http://www.phpdoc.org/phpDocumentor.phar)

- You can generate it with https://www.phpdoc.org/ by running `composer doc`
