<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
</head>
<body>

<script type="text/javascript">
    $(document).ready(function () {
        $("#myform").submit();
    });
</script>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require '../src/Client.php';

Be2bill_Api_Client::registerAutoloader();

// Use fallback URL
// Be2bill_Api_ClientBuilder::switchUrls();

// Just implement BE2BILL_IDENTIFIER and BE2BILL_PASSWORD as defined
$be2bill = Be2bill_Api_ClientBuilder::buildSandboxClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

echo $be2bill->buildPaymentFormButton(
    15387,
    'order_'.time(),
    'user_123456',
    'Payment sample',
    $htmlOptions = array(
        'SUBMIT' => array("value" => "Pay with be2bill", "style" => "display: none;"),
        'FORM' => array('id' => 'myform', "target" => "be2bill-frame")
    )
);

?>
<iframe name="be2bill-frame" />

</body>
</html>
