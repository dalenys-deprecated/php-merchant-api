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

$result=( $be2bill->refund( 'A151805', 'Refund transaction A151805', $options = array( "AMOUNT" => "50" ) ) );

?>
</pre>
</body>
</html>