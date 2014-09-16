<?php

class ExportClientTest extends PHPUnit_Framework_TestCase
{
    public function testGetTransactionsByTransactionId()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $result = $api->payment(
            '5555556778250000',
            $this->getFutureValidityDate(),
            132,
            'john doe',
            '1000',
            'order-' . time(),
            'john doe',
            'johndoe@test.com',
            '1.2.3.4',
            'desc',
            'firefox'
        );

        $result = $api->getTransactionsByTransactionId($result['TRANSACTIONID'], 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testGetTransactionsByTransactionOrderId()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $orderId = 'order-' . time();
        $result = $api->payment(
            '5555556778250000',
            $this->getFutureValidityDate(),
            132,
            'john doe',
            '1000',
            $orderId,
            'john doe',
            'johndoe@test.com',
            '1.2.3.4',
            'desc',
            'firefox'
        );

        $result = $api->getTransactionsByOrderId($orderId, 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testExportTransactions()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $result = $api->exportTransactions('2014-01-05', 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testExportChargebacks()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $result = $api->exportChargebacks('2014-01-05', 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testExportReconciliation()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $result = $api->exportReconciliation('2014-01-05', 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testExportReconciledTransactions()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $result = $api->exportReconciledTransactions('2014-01-05', 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    protected function assertTransactionSucceeded(array $params = array())
    {
        $this->assertEquals($params['EXECCODE'], '0000', 'Transaction failed with message ' . $params['MESSAGE']);
    }

    protected function getFutureValidityDate()
    {
        return date('m-y', time() + 365 * 24 * 3600);
    }
}
