[Be2bill Merchant API] (http://developer.be2bill.com/)

A simple PHP implementation of the Be2bill payment platform API.

[![Build Status](https://travis-ci.org/be2bill/php-merchant-api.svg?branch=master)](https://travis-ci.org/be2bill/php-merchant-api)
[![Latest Stable Version](https://poser.pugx.org/be2bill/php-merchant-api/v/stable)](https://packagist.org/packages/be2bill/php-merchant-api) 
[![Total Downloads](https://poser.pugx.org/be2bill/php-merchant-api/downloads)](https://packagist.org/packages/be2bill/php-merchant-api) 
[![License](https://poser.pugx.org/be2bill/php-merchant-api/license)](https://packagist.org/packages/be2bill/php-merchant-api)

## Using a simple payment form

Here is the code sample for implementing a simple 10€ payment form

```php
require 'src/Client.php';

define('BE2BILL_IDENTIFIER', 'YOUR ACCOUNT IDENTIFIER');
define('BE2BILL_PASSWORD', 'YOUR ACCOUNT PASSWORD');

Be2bill_Api_Autoloader::registerAutoloader();

// Just implement BE2BILL_IDENTIFIER and BE2BILL_PASSWORD as defined
$be2bill = Be2bill_Api_ClientBuilder::buildProductionFormClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

echo $be2bill->buildPaymentFormButton(10000, 'order_123', 'user_123456', 'Payment sample');
```

## Payment options
You can specify some additional options to the buildPaymentFormButton method.
The most useful options are:
- CREATEALIAS = yes/no => Ask for the creation of a rebilling alias (allowing one click payments or subscription like payments)
- 3DSECURE = yes/no => Ask for 3DSECURE authentication
- CARDFULLNAME => When set the card holder inputs will be filled with specified data

For the full list of options you can read the Be2bill documentation

## Sandbox environment
You can easily test your integration with the sandbox environment. This environment will simulate payments without processing any real money move.
You just have to use another builder method:

```php
$be2bill = Be2bill_Api_ClientBuilder::buildSandboxFormClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);
```

## Transaction edition
You can edit a transaction: capturing or refunding an authorization.
You should use the direct link AP:

```php
$be2bill = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);
$be2bill->capture('A1234', 'order_42', 'capturing a transaction');
```

