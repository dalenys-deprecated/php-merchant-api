<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require '../src/Dalenys/Api/Autoloader.php';

Dalenys_Api_Autoloader::registerAutoloader();

// Just implement DALENYS_IDENTIFIER and DALENYS_PASSWORD as defined
$dalenys = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient(DALENYS_IDENTIFIER, DALENYS_PASSWORD);

$result = $dalenys->oneClickPayment(
    'A142429',
    100,
    'order_48',
    'client_123456',
    'john.doe@email.com',
    '127.0.0.1',
    'Oneclick payment',
    'mozilla',
    $options = array("EXTRADATA" => "Premium user")
);

var_dump($result);
