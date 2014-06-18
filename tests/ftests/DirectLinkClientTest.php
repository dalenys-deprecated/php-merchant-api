<?php

class DirectLinkClient_FormTest extends PHPUnit_Framework_TestCase
{
    public function testPayment()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $date   = date('%m-%y', time() + 365 * 24 * 3600);
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

        $this->assertTransactionSucceeded($result);
    }

    public function testAuthorization()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $date   = date('%m-%y', time() + 365 * 24 * 3600);
        $result = $api->authorization(
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

        $this->assertTransactionSucceeded($result);
    }

    public function testCapture()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $date =
        $result = $api->authorization(
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

        $result = $api->capture($result['TRANSACTIONID'], 'order-' . time(), 'desc');
        $this->assertTransactionSucceeded($result);
    }

    public function testOneClickPayment()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $date   = date('%m-%y', time() + 365 * 24 * 3600);
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
            'firefox',
            array(
                'CREATEALIAS' => 'yes',
            )
        );

        $result = $api->oneClickPayment(
            $result['ALIAS'],
            '420',
            'order-' . time(),
            'ident',
            'test@test.com',
            '1.2.3.4',
            'desc',
            'firefox'
        );

        $this->assertTransactionSucceeded($result);
    }

    public function testSubscriptionPayment()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $date   = date('%m-%y', time() + 365 * 24 * 3600);
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
            'firefox',
            array(
                'CREATEALIAS' => 'yes',
            )
        );

        $result = $api->subscriptionPayment(
            $result['ALIAS'],
            '420',
            'order-' . time(),
            'ident',
            'test@test.com',
            '1.2.3.4',
            'desc',
            'firefox'
        );

        $this->assertTransactionSucceeded($result);
    }

    public function testOneClickAuthorization()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $date   = date('%m-%y', time() + 365 * 24 * 3600);
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
            'firefox',
            array(
                'CREATEALIAS' => 'yes',
            )
        );

        $result = $api->oneClickAuthorization(
            $result['ALIAS'],
            '420',
            'order-' . time(),
            'ident',
            'test@test.com',
            '1.2.3.4',
            'desc',
            'firefox'
        );

        $this->assertTransactionSucceeded($result);
    }

    public function testSubscriptionAuthorization()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $date   = date('%m-%y', time() + 365 * 24 * 3600);
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
            'firefox',
            array(
                'CREATEALIAS' => 'yes',
            )
        );

        $result = $api->subscriptionAuthorization(
            $result['ALIAS'],
            '420',
            'order-' . time(),
            'ident',
            'test@test.com',
            '1.2.3.4',
            'desc',
            'firefox'
        );

        $this->assertTransactionSucceeded($result);
    }

    public function testRefund()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $date =
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

        $result = $api->refund($result['TRANSACTIONID'], 'order-' . time(), 'desc');
        $this->assertTransactionSucceeded($result);
    }

    // Actually doesn't work since CLIENTREFERER is still mandatory in CREDIT method
//    public function testCredit()
//    {
//        $api = Be2bill_Api_ClientBuilder::buildSandboxDirectlinkClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);
//
//        $date =
//        $result = $api->credit(
//            '5555556778250000',
//            $this->getFutureValidityDate(),
//            132,
//            'john doe',
//            '1000',
//            'order-' . time(),
//            'john doe',
//            'johndoe@test.com',
//            '1.2.3.4',
//            'desc',
//            'firefox'
//        );
//
//        $this->assertTransactionSucceeded($result);
//    }

    protected function assertTransactionSucceeded(array $params = array())
    {
        $this->assertEquals($params['EXECCODE'], '0000', 'Transaction failed with message ' . $params['MESSAGE']);
    }

    protected function getFutureValidityDate()
    {
        return date('m-y', time() + 365 * 24 * 3600);
    }
}
