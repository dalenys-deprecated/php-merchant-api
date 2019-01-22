<?php

use PHPUnit\Framework\TestCase;

class Client_BatchTest extends TestCase
{
    protected $senderDummy;
    protected $hashStub;
    protected $directLinkMockArguments;

    public function setUp()
    {
        $this->senderDummy = $this->createMock('Be2bill_Api_Sender_Sendable');
        $this->hashStub    = $this->createMock('Be2bill_Api_Hash_Hashable');

        $this->hashStub->expects($this->any())
            ->method('compute')
            ->will($this->returnValue('dummy'));

        $this->directLinkMockArguments = array(
            'i',
            'p',
            array('http://test'),
            $this->senderDummy,
            $this->hashStub
        );
    }

    public function testSetInputFileAcceptFilePath()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $file = 'php://memory';

        $batchClient = new Be2bill_Api_BatchClient($apiMock);
        $batchClient->setInputFile($file);

        $this->assertNotEmpty($batchClient->getFile());
        $this->assertInternalType('resource', $batchClient->getInputFileDescriptor());
    }

    public function testSetInputFileAcceptFileDescriptor()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $file = fopen('php://memory', 'w+');

        $batchClient = new Be2bill_Api_BatchClient($apiMock);
        $batchClient->setInputFile($file);

        $this->assertNotEmpty($batchClient->getFile());
        $this->assertInternalType('resource', $batchClient->getInputFileDescriptor());
    }

    public function test1Transaction()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setMethods(['requestOne'])
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $apiMock->expects($this->once())
            ->method('requestOne')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'IDENTIFIER'      => 'i',
                    'ALIAS'           => 'A1',
                    'ALIASMODE'       => 'SUBSCRIPTION',
                    'OPERATIONTYPE'   => 'payment',
                    'ORDERID'         => 'oid',
                    'AMOUNT'          => 100,
                    'CLIENTIDENT'     => 'jdoe',
                    'CLIENTEMAIL'     => 'john.doe@mail.com',
                    'CLIENTIP'        => '1.2.3.4',
                    'CLIENTUSERAGENT' => 'firefox',
                    'DESCRIPTION'     => 'rebill',
                    'VERSION'         => '2.0',
                    'HASH'            => 'dummy',
                )
            )
            ->will($this->returnValue(array('EXECCODE' => '0000', 'MESSAGE' => 'OK')));

        $file = $this->generateCsv(1);

        $batchClient = new Be2bill_Api_BatchClient($apiMock);
        $batchClient->setInputFile($file);
        $batchClient->run();
    }

    public function test2Transactions()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setMethods(['requestOne'])
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $apiMock->expects($this->exactly(2))
            ->method('requestOne')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'IDENTIFIER'      => 'i',
                    'ALIAS'           => 'A1',
                    'ALIASMODE'       => 'SUBSCRIPTION',
                    'OPERATIONTYPE'   => 'payment',
                    'ORDERID'         => 'oid',
                    'AMOUNT'          => 100,
                    'CLIENTIDENT'     => 'jdoe',
                    'CLIENTEMAIL'     => 'john.doe@mail.com',
                    'CLIENTIP'        => '1.2.3.4',
                    'CLIENTUSERAGENT' => 'firefox',
                    'DESCRIPTION'     => 'rebill',
                    'VERSION'         => '2.0',
                    'HASH'            => 'dummy',
                )
            )
            ->will($this->returnValue(array('EXECCODE' => '0000', 'MESSAGE' => 'OK')));

        $file = $this->generateCsv(2);

        $batchClient = new Be2bill_Api_BatchClient($apiMock);
        $batchClient->setInputFile($file);
        $batchClient->run();
    }

    /**
     * @expectedException Be2bill_Api_Batch_Exception_InvalidBatchFile
     */
    public function testHashNotAllowedAsCsvColumn()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setMethods(['requestOne'])
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $file = $this->generateCsv(1, array('k0' => array('HASH' => 'toto')));

        $batchClient = new Be2bill_Api_BatchClient($apiMock);
        $batchClient->setInputFile($file);
        $batchClient->run();
    }

    /**
     * @expectedException Be2bill_Api_Batch_Exception_InvalidBatchFile
     */
    public function testIdentifierNotAllowedAsCsvColumn()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setMethods(['requestOne'])
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $file = $this->generateCsv(1, array('k0' => array('IDENTIFIER' => 'toto')));

        $batchClient = new Be2bill_Api_BatchClient($apiMock);
        $batchClient->setInputFile($file);
        $batchClient->run();
    }

    public function testDoNotSendEmptyColumns()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setMethods(['requestOne'])
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $apiMock->expects($this->exactly(1))
            ->method('requestOne')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'IDENTIFIER'      => 'i',
                    'ALIAS'           => 'A1',
                    'ALIASMODE'       => 'SUBSCRIPTION',
                    'OPERATIONTYPE'   => 'payment',
                    'ORDERID'         => 'oid',
                    'AMOUNT'          => 100,
                    'CLIENTIDENT'     => 'jdoe',
                    'CLIENTEMAIL'     => 'john.doe@mail.com',
                    'CLIENTIP'        => '1.2.3.4',
                    'CLIENTUSERAGENT' => 'firefox',
                    'DESCRIPTION'     => 'rebill',
                    'VERSION'         => '2.0',
                    'HASH'            => 'dummy',
                )
            )
            ->will($this->returnValue(array('EXECCODE' => '0000', 'MESSAGE' => 'OK')));

        $file = $this->generateCsv(1, array('k0' => array('CACA' => '')));

        $batchClient = new Be2bill_Api_BatchClient($apiMock);
        $batchClient->setInputFile($file);
        $batchClient->run();
    }

    public function testNotifyRealTimeTransactions()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setMethods(['requestOne'])
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $apiMock->expects($this->exactly(5))
            ->method('requestOne')
            ->with(
                'http://test/front/service/rest/process',
                array(
                    'IDENTIFIER'      => 'i',
                    'ALIAS'           => 'A1',
                    'ALIASMODE'       => 'SUBSCRIPTION',
                    'OPERATIONTYPE'   => 'payment',
                    'ORDERID'         => 'oid',
                    'AMOUNT'          => 100,
                    'CLIENTIDENT'     => 'jdoe',
                    'CLIENTEMAIL'     => 'john.doe@mail.com',
                    'CLIENTIP'        => '1.2.3.4',
                    'CLIENTUSERAGENT' => 'firefox',
                    'DESCRIPTION'     => 'rebill',
                    'VERSION'         => '2.0',
                    'HASH'            => 'dummy',
                )
            )
            ->will($this->returnValue(array('EXECCODE' => '0000', 'MESSAGE' => 'OK')));

        $batchClient = new Be2bill_Api_BatchClient($apiMock);

        $observerMock = $this->createMock('SplObserver');
        $observerMock->expects($this->exactly(5))
            ->method('update')
            ->with($this->isInstanceOf('Be2bill_Api_BatchClient'));

        $file = $this->generateCsv(5);

        $batchClient->setInputFile($file);
        $batchClient->attach($observerMock);
        $batchClient->run();
    }

    public function testSkipEmptyLine()
    {
        $apiMock = $this->getMockBuilder('Be2bill_Api_DirectLinkClient')
            ->setMethods(['requestOne'])
            ->setConstructorArgs($this->directLinkMockArguments)
            ->getMock();

        $apiMock->expects($this->exactly(3))
            ->method('requestOne');

        $batchClient = new Be2bill_Api_BatchClient($apiMock);

        $observerMock = $this->createMock('SplObserver');
        $observerMock->expects($this->exactly(3))
            ->method('update')
            ->with($this->isInstanceOf('Be2bill_Api_BatchClient'));

        $file = fopen('php://memory', 'w+');
        fwrite($file, "AMOUNT;ORDERID;CARDCPDE\n");
        fwrite($file, "AMOUNT;ORDERID;CARDCPDE\n");
        fwrite($file, "AMOUNT;ORDERID;CARDCPDE\n");
        fwrite($file, "\n");
        fwrite($file, "\n");
        fwrite($file, "\n");
        fwrite($file, "\n");
        fwrite($file, "AMOUNT;ORDERID;CARDCPDE\n");
        fwrite($file, "\n");
        fwrite($file, "\n");
        rewind($file);

        $batchClient->setInputFile($file);
        $batchClient->attach($observerMock);
        $batchClient->run();
    }

    /**
     * Generate Nb line of CSV
     * @param $nb
     * @param array $array useful to overload some lines
     * @return ressource
     */
    protected function generateCsv($nb, array $array = array())
    {
        $params = array();

        for ($i = 0; $i < $nb; $i++) {

            //HACK: array_merge_recursive doesn't merge arrays when keys are numeric... so use k1,k2 ... as keys
            $params["k{$i}"] = array(
                'ALIAS'           => 'A1',
                'ALIASMODE'       => 'SUBSCRIPTION',
                'OPERATIONTYPE'   => 'payment',
                'ORDERID'         => 'oid',
                'AMOUNT'          => 100,
                'CLIENTIDENT'     => 'jdoe',
                'CLIENTEMAIL'     => 'john.doe@mail.com',
                'CLIENTIP'        => '1.2.3.4',
                'CLIENTUSERAGENT' => 'firefox',
                'DESCRIPTION'     => 'rebill',
                'VERSION'         => '2.0',
            );
        }

        $params = array_merge_recursive($params, $array);

        $file = fopen('php://memory', 'w+');

        fputcsv($file, array_keys(current($params)), ';');

        foreach ($params as $line) {
            fputcsv($file, $line, ';');
        }

        rewind($file);

        return $file;
    }
}
