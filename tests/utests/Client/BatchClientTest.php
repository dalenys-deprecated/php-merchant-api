<?php

class Client_BatchTest extends PHPUnit_Framework_TestCase
{
    protected $senderDummy;
    protected $hashStub;
    protected $directLinkMockArguments;

    public function setUp()
    {
        $this->senderDummy = $this->getMock('Be2bill_Api_Sender_Sendable');
        $this->hashStub   = $this->getMock('Be2bill_Api_Hash_Hashable');

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

    public function test1Transaction()
    {
        $apiMock = $this->getMock('Be2bill_Api_DirectLinkClient', array('requestOne'), $this->directLinkMockArguments);

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
        $apiMock = $this->getMock('Be2bill_Api_DirectLinkClient', array('requestOne'), $this->directLinkMockArguments);

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
     * @expectedException Be2bill_Api_Exception_InvalidBatchFile
     */
    public function testIdentifierNotAllowedAsCsvColumn()
    {
        $apiMock = $this->getMock('Be2bill_Api_DirectLinkClient', array('requestOne'), $this->directLinkMockArguments);

        $file = $this->generateCsv(1, array('k0' => array('IDENTIFIER' => 'toto')));

        $batchClient = new Be2bill_Api_BatchClient($apiMock);
        $batchClient->setInputFile($file);
        $batchClient->run();
    }

    /**
     * Generate Nb line of CSV
     * @param $nb
     * @param array $array useful to overload some lines
     * @return SplTempFileObject
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

        $file = new SplTempFileObject();
        $file->setCsvControl(';');

        $file->fputcsv(array_keys(current($params)));

        foreach ($params as $line) {
            $file->fputcsv($line);
        }

        $file->rewind();

        return $file;
    }
}
