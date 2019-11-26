<?php

class DirectLinkClientTest extends Dalenys_Api_Test_FCase
{
    public function testPayment()
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

        $this->assertTransactionSucceeded($result);
    }

    public function testAuthorization()
    {
        $result = $this->api->authorization(
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

        $this->assertTransactionSucceeded($result);
    }

    public function testCapture()
    {
        $result = $this->api->authorization(
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

        $result = $this->api->capture($result['TRANSACTIONID'], 'order-' . time(), 'desc');
        $this->assertTransactionSucceeded($result);
    }

    public function testOneClickPayment()
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
            'firefox',
            array(
                'CREATEALIAS' => 'yes',
            )
        );

        $result = $this->api->oneClickPayment(
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
            'firefox',
            array(
                'CREATEALIAS' => 'yes',
            )
        );

        $result = $this->api->subscriptionPayment(
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
            'firefox',
            array(
                'CREATEALIAS' => 'yes',
            )
        );

        $result = $this->api->oneClickAuthorization(
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
            'firefox',
            array(
                'CREATEALIAS' => 'yes',
            )
        );

        $result = $this->api->subscriptionAuthorization(
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
        $api   = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient(
            DALENYS_TEST_IDENTIFIER,
            DALENYS_TEST_PASSWORD
        );
        $tools = new Dalenys_Api_Test_Tools();

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

        $result = $this->api->refund($result['TRANSACTIONID'], 'order-' . time(), 'desc');
        $this->assertTransactionSucceeded($result);
    }

    public function testCredit()
    {
        $result = $this->api->credit(
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

        $this->assertTransactionSucceeded($result);
    }

    protected function getIdentifier()
    {
        return DALENYS_TEST_IDENTIFIER;
    }

    protected function getPassword()
    {
        return DALENYS_TEST_PASSWORD;
    }
}
