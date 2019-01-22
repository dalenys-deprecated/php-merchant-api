<?php

use PHPUnit\Framework\TestCase;

class Client_ExportTest extends TestCase
{
    protected $hashStub = null;
    protected $senderMock = null;

    /**
     * @var Be2bill_Api_FormClient
     */
    protected $api = null;

    public function setUp()
    {
        $this->senderMock = $this->createMock('Be2bill_Api_Sender_Sendable');
        $this->hashStub   = $this->createMock('Be2bill_Api_Hash_Hashable');

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

    public function testGetTransactionsByTransactionIdWithDirectReturn()
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
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->getTransactionsByTransactionId('A1');
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
                'http://test/front/service/rest/export',
                array(
                    'method' => 'exportChargebacks',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'DATE'          => '2014-01-02',
                        'OPERATIONTYPE' => 'exportChargebacks',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->exportChargebacks('2014-01-02', 'test@test.com');
    }

    public function testExportReconciliation()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/reconciliation',
                array(
                    'method' => 'exportReconciliation',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'DATE'          => '2014-01-02',
                        'OPERATIONTYPE' => 'exportReconciliation',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->exportReconciliation('2014-01-02', 'test@test.com');
    }

    public function testExportReconciledTransactions()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/reconciliation',
                array(
                    'method' => 'exportReconciledTransactions',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'DATE'          => '2014-01-02',
                        'OPERATIONTYPE' => 'exportReconciledTransactions',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->exportReconciledTransactions('2014-01-02', 'test@test.com');
    }

    public function testExportWithDateInterval()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->with(
                'http://test/front/service/rest/export',
                array(
                    'method' => 'exportTransactions',
                    'params' => array(
                        'IDENTIFIER'    => 'i',
                        'STARTDATE'     => '2014-01-02',
                        'ENDDATE'       => '2014-01-05',
                        'OPERATIONTYPE' => 'exportTransactions',
                        'COMPRESSION'   => 'GZIP',
                        'MAILTO'        => 'test@test.com',
                        'VERSION'       => '2.0',
                        'HASH'          => 'dummy'
                    )
                )
            );

        $this->api->exportTransactions(array('2014-01-02', '2014-01-05'), 'test@test.com');
    }
}
