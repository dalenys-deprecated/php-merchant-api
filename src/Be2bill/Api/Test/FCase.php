<?php

abstract class Be2bill_Api_Test_FCase extends PHPUnit_Framework_TestCase
{
    protected $api = null;
    protected $tools = null;

    abstract protected function getIdentifier();

    abstract protected function getPassword();

    public function __construct()
    {
        parent::__construct();

        $this->api   = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(
            $this->getIdentifier(),
            $this->getPassword()
        );
        $this->tools = new Be2bill_Api_Test_Tools();
    }

    protected function assertTransactionSucceeded(array $params = array())
    {
        $this->assertEquals($params['EXECCODE'], '0000', 'Transaction failed with message ' . $params['MESSAGE']);
    }
}
