<?php

/**
 * Functional test tools
 *
 * @package Be2bill
 * @subpackage Test tools
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Test base class for functionnal testing
 *
 * Handle IDENTIFIER, PASSWORD, Client instanciation and test tools
 */
abstract class Be2bill_Api_Test_FCase extends PHPUnit_Framework_TestCase
{
    /**
     * API Directlink client
     * @var Be2bill_Api_DirectLinkClient
     */
    protected $api;

    /**
     * Test tools
     * @var Be2bill_Api_Test_Tools
     */
    protected $tools;

    /**
     * Get Be2bill identifier
     * @return string identifier
     */
    abstract protected function getIdentifier();

    /**
     * Get Be2bill password
     * @return mixed
     */
    abstract protected function getPassword();

    /**
     * Instanciate
     */
    public function __construct()
    {
        parent::__construct();

        $this->api   = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(
            $this->getIdentifier(),
            $this->getPassword()
        );
        $this->tools = new Be2bill_Api_Test_Tools();
    }

    /**
     * Test a succeeded transaction
     *
     * @param array $params
     */
    protected function assertTransactionSucceeded(array $params = array())
    {
        $this->assertEquals($params['EXECCODE'], '0000', 'Transaction failed with message ' . $params['MESSAGE']);
    }
}
