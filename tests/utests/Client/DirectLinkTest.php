<?php

class Client_DirectLinkTest extends PHPUnit_Framework_TestCase
{
    protected $hashStub = null;
    protected $senderMock = null;

    /**
     * @var Be2bill_Api_DirectLinkClient
     */
    protected $api = null;

    public function setUp()
    {
        $this->senderMock = $this->getMock('Be2bill_Api_Sender_Sendable');
        $this->hashStub   = $this->getMock('Be2bill_Api_Hash_Hashable');

        $this->hashStub->expects($this->once())
            ->method('compute')
            ->will($this->returnValue('dummy'));

        $this->api = new Be2bill_Api_DirectLinkClient(
            'i',
            'p',
            array('http://test'),
            $this->senderMock,
            $this->hashStub
        );
    }

    public function testPayment()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'payment',
                    'params' => array(
                        'AMOUNT'           => 100,
                        'IDENTIFIER'       => 'i',
                        'OPERATIONTYPE'    => 'payment',
                        'ORDERID'          => 42,
                        'CLIENTIDENT'      => 'ident',
                        'CLIENTEMAIL'      => 'test@test.com',
                        'CLIENTIP'         => '1.1.1.1',
                        'CLIENTUSERAGENT'  => 'Firefox',
                        'VERSION'          => '2.0',
                        'HASH'             => 'dummy',
                        'DESCRIPTION'      => 'desc',
                        'CARDCODE'         => '1111222233334444',
                        'CARDCVV'          => '123',
                        'CARDVALIDITYDATE' => '01-12',
                        'CARDFULLNAME'     => 'john doe'
                    )
                )
            );

        $this->api->payment(
            '1111222233334444',
            '01-12',
            '123',
            'john doe',
            100,
            42,
            'ident',
            'test@test.com',
            '1.1.1.1',
            'desc',
            'Firefox'
        );
    }

    public function testAuthorization()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'authorization',
                    'params' => array(
                        'AMOUNT'           => 100,
                        'IDENTIFIER'       => 'i',
                        'OPERATIONTYPE'    => 'authorization',
                        'ORDERID'          => 42,
                        'CLIENTIDENT'      => 'ident',
                        'CLIENTEMAIL'      => 'test@test.com',
                        'CLIENTIP'         => '1.1.1.1',
                        'CLIENTUSERAGENT'  => 'Firefox',
                        'VERSION'          => '2.0',
                        'HASH'             => 'dummy',
                        'DESCRIPTION'      => 'desc',
                        'CARDCODE'         => '1111222233334444',
                        'CARDCVV'          => '123',
                        'CARDVALIDITYDATE' => '01-12',
                        'CARDFULLNAME'     => 'john doe'
                    )
                )
            );

        $this->api->authorization(
            '1111222233334444',
            '01-12',
            '123',
            'john doe',
            100,
            42,
            'ident',
            'test@test.com',
            '1.1.1.1',
            'desc',
            'Firefox'
        );
    }

    public function testCredit()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'credit',
                    'params' => array(
                        'AMOUNT'           => 100,
                        'IDENTIFIER'       => 'i',
                        'OPERATIONTYPE'    => 'credit',
                        'ORDERID'          => 42,
                        'CLIENTIDENT'      => 'ident',
                        'CLIENTEMAIL'      => 'test@test.com',
                        'CLIENTIP'         => '1.1.1.1',
                        'CLIENTUSERAGENT'  => 'Firefox',
                        'VERSION'          => '2.0',
                        'HASH'             => 'dummy',
                        'DESCRIPTION'      => 'desc',
                        'CARDCODE'         => '1111222233334444',
                        'CARDCVV'          => '123',
                        'CARDVALIDITYDATE' => '01-12',
                        'CARDFULLNAME'     => 'john doe'
                    )
                )
            );

        $this->api->credit(
            '1111222233334444',
            '01-12',
            '123',
            'john doe',
            100,
            42,
            'ident',
            'test@test.com',
            '1.1.1.1',
            'desc',
            'Firefox'
        );
    }

    public function testCapture()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'capture',
                    'params' => array(
                        'TRANSACTIONID' => 'A1',
                        'IDENTIFIER'    => 'i',
                        'OPERATIONTYPE' => 'capture',
                        'ORDERID'       => 42,
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy',
                        'DESCRIPTION'   => 'desc'
                    )
                )
            );

        $this->api->capture('A1', 42, 'desc');
    }

    public function testOneClickPayment()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'payment',
                    'params' => array(
                        'ALIAS'           => 'A1',
                        'ALIASMODE'       => 'oneclick',
                        'AMOUNT'          => 100,
                        'IDENTIFIER'      => 'i',
                        'OPERATIONTYPE'   => 'payment',
                        'ORDERID'         => 42,
                        'CLIENTIDENT'     => 'ident',
                        'CLIENTEMAIL'     => 'test@test.com',
                        'CLIENTIP'        => '1.1.1.1',
                        'CLIENTUSERAGENT' => 'Firefox',
                        'VERSION'         => '2.0',
                        'HASH'            => 'dummy',
                        'DESCRIPTION'     => 'desc'
                    )
                )
            );

        $this->api->oneClickPayment('A1', 100, 42, 'ident', 'test@test.com', '1.1.1.1', 'desc', 'Firefox');
    }

    public function testOneClickPaymentWithNTime()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'payment',
                    'params' => array(
                        'ALIAS'           => 'A1',
                        'ALIASMODE'       => 'oneclick',
                        'AMOUNTS'         => array(
                            '2015-01-01' => 100,
                            '2015-02-01' => 100,
                            '2015-03-01' => 100,
                        ),
                        'IDENTIFIER'      => 'i',
                        'OPERATIONTYPE'   => 'payment',
                        'ORDERID'         => 42,
                        'CLIENTIDENT'     => 'ident',
                        'CLIENTEMAIL'     => 'test@test.com',
                        'CLIENTIP'        => '1.1.1.1',
                        'CLIENTUSERAGENT' => 'Firefox',
                        'VERSION'         => '2.0',
                        'HASH'            => 'dummy',
                        'DESCRIPTION'     => 'desc'
                    )
                )
            );

        $amounts = array(
            '2015-01-01' => 100,
            '2015-02-01' => 100,
            '2015-03-01' => 100,
        );
        $this->api->oneClickPayment('A1', $amounts, 42, 'ident', 'test@test.com', '1.1.1.1', 'desc', 'Firefox');
    }

    public function testSubscriptionPayment()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'payment',
                    'params' => array(
                        'ALIAS'           => 'A1',
                        'ALIASMODE'       => 'subscription',
                        'AMOUNT'          => 100,
                        'IDENTIFIER'      => 'i',
                        'OPERATIONTYPE'   => 'payment',
                        'ORDERID'         => 42,
                        'CLIENTIDENT'     => 'ident',
                        'CLIENTEMAIL'     => 'test@test.com',
                        'CLIENTIP'        => '1.1.1.1',
                        'CLIENTUSERAGENT' => 'Firefox',
                        'VERSION'         => '2.0',
                        'HASH'            => 'dummy',
                        'DESCRIPTION'     => 'desc'
                    )
                )
            );

        $this->api->subscriptionPayment('A1', 100, 42, 'ident', 'test@test.com', '1.1.1.1', 'desc', 'Firefox');
    }

    public function testSubscriptionPaymentWithNTime()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'payment',
                    'params' => array(
                        'ALIAS'           => 'A1',
                        'ALIASMODE'       => 'subscription',
                        'AMOUNTS'         => array(
                            '2015-01-01' => 100,
                            '2015-02-01' => 100,
                            '2015-03-01' => 100,
                        ),
                        'IDENTIFIER'      => 'i',
                        'OPERATIONTYPE'   => 'payment',
                        'ORDERID'         => 42,
                        'CLIENTIDENT'     => 'ident',
                        'CLIENTEMAIL'     => 'test@test.com',
                        'CLIENTIP'        => '1.1.1.1',
                        'CLIENTUSERAGENT' => 'Firefox',
                        'VERSION'         => '2.0',
                        'HASH'            => 'dummy',
                        'DESCRIPTION'     => 'desc'
                    )
                )
            );

        $amounts = array(
            '2015-01-01' => 100,
            '2015-02-01' => 100,
            '2015-03-01' => 100,
        );
        $this->api->subscriptionPayment('A1', $amounts, 42, 'ident', 'test@test.com', '1.1.1.1', 'desc', 'Firefox');
    }

    public function testOneClickAuthorization()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'authorization',
                    'params' => array(
                        'ALIAS'           => 'A1',
                        'ALIASMODE'       => 'oneclick',
                        'AMOUNT'          => 100,
                        'IDENTIFIER'      => 'i',
                        'OPERATIONTYPE'   => 'authorization',
                        'ORDERID'         => 42,
                        'CLIENTIDENT'     => 'ident',
                        'CLIENTEMAIL'     => 'test@test.com',
                        'CLIENTIP'        => '1.1.1.1',
                        'CLIENTUSERAGENT' => 'Firefox',
                        'VERSION'         => '2.0',
                        'HASH'            => 'dummy',
                        'DESCRIPTION'     => 'desc'
                    )
                )
            );

        $this->api->oneClickAuthorization('A1', 100, 42, 'ident', 'test@test.com', '1.1.1.1', 'desc', 'Firefox');
    }

    public function testSubscriptionAuthorization()
    {

        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'authorization',
                    'params' => array(
                        'ALIAS'           => 'A1',
                        'ALIASMODE'       => 'subscription',
                        'AMOUNT'          => 100,
                        'IDENTIFIER'      => 'i',
                        'OPERATIONTYPE'   => 'authorization',
                        'ORDERID'         => 42,
                        'CLIENTIDENT'     => 'ident',
                        'CLIENTEMAIL'     => 'test@test.com',
                        'CLIENTIP'        => '1.1.1.1',
                        'CLIENTUSERAGENT' => 'Firefox',
                        'VERSION'         => '2.0',
                        'HASH'            => 'dummy',
                        'DESCRIPTION'     => 'desc'
                    )
                )
            );

        $this->api->subscriptionAuthorization('A1', 100, 42, 'ident', 'test@test.com', '1.1.1.1', 'desc', 'Firefox');
    }

    public function testRefund()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'refund',
                    'params' => array(
                        'TRANSACTIONID' => 'A1',
                        'IDENTIFIER'    => 'i',
                        'OPERATIONTYPE' => 'refund',
                        'ORDERID'       => 42,
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy',
                        'DESCRIPTION'   => 'desc'
                    )
                )
            );

        $this->api->refund('A1', 42, 'desc');
    }

    public function testStopNTimes()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'stopntimes',
                    'params' => array(
                        'SCHEDULEID'    => 'A1',
                        'IDENTIFIER'    => 'i',
                        'OPERATIONTYPE' => 'stopntimes',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy',
                    )
                )
            );

        $this->api->stopNTimes('A1');
    }

    public function testRedirectForPayment()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'payment',
                    'params' => array(
                        'AMOUNT'          => 100,
                        'IDENTIFIER'      => 'i',
                        'OPERATIONTYPE'   => 'payment',
                        'ORDERID'         => 42,
                        'CLIENTIDENT'     => 'ident',
                        'CLIENTEMAIL'     => 'test@test.com',
                        'CLIENTIP'        => '1.1.1.1',
                        'CLIENTUSERAGENT' => 'Firefox',
                        'VERSION'         => '2.0',
                        'HASH'            => 'dummy',
                        'DESCRIPTION'     => 'desc'
                    )
                )
            );

        $this->api->redirectForPayment(100, 42, 'ident', 'test@test.com', '1.1.1.1', 'desc', 'Firefox');
    }

    public function testTwoRequestsWhenRequestOneFailWithDirectLink()
    {
        $this->api->setUrls(
            array(
                'http://test',
                'http://test'
            )
        );

        $this->senderMock->expects($this->exactly(2))
            ->method('shouldRetry')
            ->will($this->returnValue(true));

        $this->senderMock->expects($this->exactly(2))
            ->method('send')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'method' => 'capture',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'TRANSACTIONID' => 'A1',
                        'OPERATIONTYPE' => 'capture',
                        'ORDERID'       => 42,
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy',
                        'DESCRIPTION'   => 'desc'
                    )
                )
            )
            ->will($this->returnValueMap(array(false, array('CODE' => '0000'))));

        $this->api->capture('A1', 42, 'desc');
    }
}
