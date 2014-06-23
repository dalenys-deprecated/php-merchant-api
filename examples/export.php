<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require '../src/Autoloader.php';

Be2bill_Api_Autoloader::registerAutoloader();

// Just implement BE2BILL_IDENTIFIER and BE2BILL_PASSWORD as defined
$be2bill = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

var_dump($be2bill->getTransactionsByTransactionId('A151805', 'your@mail.com'));
