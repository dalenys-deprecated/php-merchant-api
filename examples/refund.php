<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require '../src/Dalenys/Api/Autoloader.php';

Dalenys_Api_Autoloader::registerAutoloader();

// Just implement DALENYS_IDENTIFIER and DALENYS_PASSWORD as defined
$dalenys = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient(DALENYS_IDENTIFIER, DALENYS_PASSWORD);

$result = $dalenys->refund('A151805', 'order-42', 'Refund transaction A151805', $options = array("AMOUNT" => "50"));

var_dump($result);
