<?php

class ExportClientTest extends Be2bill_Api_Test_FCase
{
    public function testGetTransactionsByTransactionId()
    {
        $result = $this->api->payment(
            '5555556778250000',
            $this->tools->getFutureValidityDate(),
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

        $result = $this->api->getTransactionsByTransactionId($result['TRANSACTIONID'], 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testGetTransactionsByTransactionIdWithDirectReturn()
    {
        // HACK: use a TRANSACTIONID (because of ETL delay)
        $result = $this->api->getTransactionsByTransactionId("A1440894");

        $this->assertTransactionSucceeded($result);
        $this->assertEquals(1, sizeof($result['DATA']));
    }

    public function testGetTransactionsByTransactionOrderId()
    {
        $orderId = 'order-' . time();
        $result  = $this->api->payment(
            '5555556778250000',
            $this->tools->getFutureValidityDate(),
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

        $result = $this->api->getTransactionsByOrderId($orderId, 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testExportTransactions()
    {
        $result = $this->api->exportTransactions('2014-01-05', 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testExportChargebacks()
    {
        $result = $this->api->exportChargebacks('2014-01-05', 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testExportReconciliation()
    {
        $result = $this->api->exportReconciliation('2015-10-05', 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    public function testExportReconciledTransactions()
    {
        $result = $this->api->exportReconciledTransactions('2015-10-05', 'no-reply@be2bill.com');

        $this->assertTransactionSucceeded($result);
    }

    protected function getIdentifier()
    {
        return BE2BILL_TEST_IDENTIFIER;
    }

    protected function getPassword()
    {
        return BE2BILL_TEST_PASSWORD;
    }
}
