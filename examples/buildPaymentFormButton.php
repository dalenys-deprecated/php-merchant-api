<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require '../src/Be2bill/Api/Autoloader.php';

Be2bill_Api_Autoloader::registerAutoloader();

// Use fallback URL
// Be2bill_Api_ClientBuilder::switchProductionUrls();

// Just implement BE2BILL_IDENTIFIER and BE2BILL_PASSWORD as defined
$be2bill = Be2bill_Api_ClientBuilder::buildSandboxFormClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

echo $be2bill->buildPaymentFormButton(
    15387,
    'order_'.time(),
    'user_123456',
    'Payment sample',
    $htmlOptions = array(
        'SUBMIT' => array("value" => "Pay with be2bill"),
        'FORM' => array('id' => 'myform')
    ),
    $options = array(
        "3DSECURE"         => "yes",
        "CARDFULLNAME"     => "John Doe",
        "CLIENTEMAIL"      => "toto@pouet.com",
        "HIDECARDFULLNAME" => "yes",
        "HIDECLIENTEMAIL"  => "yes"
    )
);

?>
</body>
</html>
