<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require '../src/Dalenys/Api/Autoloader.php';

Dalenys_Api_Autoloader::registerAutoloader();

// Use fallback URL
// Dalenys_Api_ClientBuilder::switchProductionUrls();

// Just implement DALENYS_IDENTIFIER and DALENYS_PASSWORD as defined
$dalenys = Dalenys_Api_ClientBuilder::buildSandboxFormClient(DALENYS_IDENTIFIER, DALENYS_PASSWORD);

echo $dalenys->buildPaymentFormButton(
    15387,
    'order_'.time(),
    'user_123456',
    'Payment sample',
    $htmlOptions = array(
        'SUBMIT' => array("value" => "Pay with dalenys"),
        'FORM' => array('id' => 'myform')
    ),
    $options = array(
        "3DSECURE"         => "yes",
        "CARDFULLNAME"     => "John Doe",
        "CLIENTEMAIL"      => "john.doe@email.com",
        "HIDECARDFULLNAME" => "yes",
        "HIDECLIENTEMAIL"  => "yes"
    )
);

?>
</body>
</html>
