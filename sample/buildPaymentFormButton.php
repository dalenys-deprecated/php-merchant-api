<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<pre>
<?php

require 'config.php';
require '../be2bill.php';

// Just implement BE2BILL_IDENTIFIER and BE2BILL_PASSWORD as defined
$be2bill = new be2bill(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

// Set up the environment
$be2bill->setUrls('https://secure-test.be2bill.com');

echo $be2bill->buildPaymentFormButton( 15387, 'client_123456', 'My basket', $htmlOptions = array( "value" => "Pay with be2bill" ), $options = array( "3DSECURE" => "yes", "CARDFULLNAME" => "John Doe", "CLIENTEMAIL" => "toto@pouet.com", "CREATEALIAS" => "yes", "HIDECARDFULLNAME" => "yes", "HIDECLIENTEMAIL" => "yes" ) );

?>
</pre>
</body>
</html>