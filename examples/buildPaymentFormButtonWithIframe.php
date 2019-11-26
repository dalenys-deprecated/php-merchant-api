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
require '../src/Dalenys/Api/Autoloader.php';

Dalenys_Api_Autoloader::registerAutoloader();

// Use fallback URL
// Dalenys_Api_ClientBuilder::switchUrls();

// Just implement DALENYS_IDENTIFIER and DALENYS_PASSWORD as defined
$dalenys = Dalenys_Api_ClientBuilder::buildSandboxFormClient(DALENYS_IDENTIFIER, DALENYS_PASSWORD);

echo $dalenys->buildPaymentFormButton(
    15387,
    'order_'.time(),
    'user_123456',
    'Payment sample',
    $htmlOptions = array(
        'SUBMIT' => array("value" => "Pay with dalenys", "style" => "display: none;"),
        'FORM' => array('id' => 'myform', "target" => "dalenys-frame")
    )
);

?>
<iframe name="dalenys-frame" />

</body>
</html>
