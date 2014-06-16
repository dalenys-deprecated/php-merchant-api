<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require '../src/Client.php';

if (!empty($_POST)) {
    error_log('POST:' . var_export($_POST, true), 3, '/tmp/debug.txt');
    error_log('POST:' . var_export($_FILES, true), 3, '/tmp/debug.txt');
} else {
    Be2bill_Api_Client::registerAutoloader();

    // Just implement BE2BILL_IDENTIFIER and BE2BILL_PASSWORD as defined
    $be2bill = Be2bill_Api_ClientBuilder::buildSandboxClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

    var_dump($be2bill->getTransactionsByTransactionId('A151805', 'jeremy@rentabiliweb.com'));
}
