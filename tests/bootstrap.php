<?php

error_reporting(E_ALL | E_STRICT);

require dirname(__FILE__) . '/../src/Be2bill/Api/Autoloader.php';
require dirname(__FILE__) . '/ftests/config.php';

Be2bill_Api_Autoloader::registerAutoloader();
