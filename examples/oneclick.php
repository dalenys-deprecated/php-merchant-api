<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require '../src/Autoloader.php';

Be2bill_Api_Autoloader::registerAutoloader();

// Just implement BE2BILL_IDENTIFIER and BE2BILL_PASSWORD as defined
$be2bill = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

$result = $be2bill->oneClickPayment(
    'A142429',
    100,
    'order_48',
    'client_123456',
    'john.doe@email.com',
    'Oneclick payment',
    '127.0.0.1',
    'mozilla',
    $options = array("EXTRADATA" => "Premium user")
);

var_dump($result);
