<?php

class Client_ExportLinkTest extends PHPUnit_Framework_TestCase
{
    protected $renderDummy = null;
    protected $hashStub = null;
    protected $senderMock = null;

    /**
     * @var Be2bill_Api_Client
     */
    protected $api = null;

    public function setUp()
    {
        $this->renderDummy = $this->getMock('Be2bill_Api_Renderer_Renderable');
        $this->senderMock  = $this->getMock('Be2bill_Api_Sender_Sendable');
        $this->hashStub    = $this->getMock('Be2bill_Api_Hash_Hashable');

        $this->hashStub->expects($this->once())
            ->method('compute')
            ->will($this->returnValue('dummy'));

        $this->api = new Be2bill_Api_Client(
            'i', 'p',
            array('http://test'),
            $this->renderDummy,
            $this->senderMock,
            $this->hashStub
        );
    }

    public function testGetTransactionsByTransactionIdWithMail()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/export',
                array(
                    'method' => 'getTransactions',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'TRANSACTIONID' => 'A1',
                        'OPERATIONTYPE' => 'getTransactions',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->getTransactionsByTransactionId('A1', 'test@test.com');
    }

    public function testGetTransactionsByTransactionIdWithCallback()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/export',
                array(
                    'method' => 'getTransactions',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'TRANSACTIONID' => 'A1',
                        'OPERATIONTYPE' => 'getTransactions',
                        'COMPRESSION'   => 'GZIP',
                        'CALLBACKURL'   => 'http://test.com/',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->getTransactionsByTransactionId('A1', 'http://test.com/');
    }

    public function testGetTransactionsByOrderId()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/export',
                array(
                    'method' => 'getTransactions',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'ORDERID'       => 'oid',
                        'OPERATIONTYPE' => 'getTransactions',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->getTransactionsByOrderId('oid', 'test@test.com');
    }

    public function testGetTransactionsByOrderIdWithSomeOrderIds()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/export',
                array(
                    'method' => 'getTransactions',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'ORDERID'       => 'a;b;c',
                        'OPERATIONTYPE' => 'getTransactions',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->getTransactionsByOrderId(array('a', 'b', 'c'), 'test@test.com');
    }

    public function testExportTransactions()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/export',
                array(
                    'method' => 'exportTransactions',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'DATE'          => '2014-01-02',
                        'OPERATIONTYPE' => 'exportTransactions',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->exportTransactions('2014-01-02', 'test@test.com');
    }

    public function testExportChargebacks()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/reconciliation',
                array(
                    'method' => 'export',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'DATE'          => '2014-01-02',
                        'OPERATIONTYPE' => 'export',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->exportReconciliation('2014-01-02', 'test@test.com');
    }
}
