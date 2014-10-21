<?php

class BatchClientTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->tools = new Be2bill_Api_Test_Tools();
    }

    public function test5Transactions()
    {
        $inputFile = new SplTempFileObject();
        $inputFile->setCsvControl(';');

        $outputFile = new SplTempFileObject();
        $outputFile->setCsvControl(';');

        $csv = array(
            array(
                'AMOUNT'           => 100,
                'OPERATIONTYPE'    => 'payment',
                'CARDCODE'         => '5555556778250000',
                'CARDVALIDITYDATE' => $this->tools->getFutureValidityDate(),
                'CARDCVV'          => '123',
                'CARDFULLNAME'     => 'John Doe',
                'ORDERID'          => 'order_' . time(),
                'CLIENTIDENT'      => 'john.doe',
                'CLIENTEMAIL'      => 'john.doe42',
                'CLIENTEMAIL'      => 'john.doe@mail.com',
                'DESCRIPTION'      => 'Test',
                'CLIENTUSERAGENT'  => 'firefox',
                'CLIENTIP'         => '1.2.3.4',
                'VERSION'          => '2.0'
            ),
            array(
                // Insuffisient funds
                'AMOUNT'           => 100,
                'OPERATIONTYPE'    => 'payment',
                'CARDCODE'         => '5555554530114002',
                'CARDVALIDITYDATE' => $this->tools->getFutureValidityDate(),
                'CARDCVV'          => '123',
                'CARDFULLNAME'     => 'John Doe',
                'ORDERID'          => 'order_' . time(),
                'CLIENTIDENT'      => 'john.doe',
                'CLIENTEMAIL'      => 'john.doe42',
                'CLIENTEMAIL'      => 'john.doe@mail.com',
                'DESCRIPTION'      => 'Test',
                'CLIENTUSERAGENT'  => 'firefox',
                'CLIENTIP'         => '1.2.3.4',
                'VERSION'          => '2.0'
            ),
            array(
                // Missing card code
                'AMOUNT'           => 100,
                'OPERATIONTYPE'    => 'payment',
                'CARDCODE'         => '',
                'CARDVALIDITYDATE' => $this->tools->getFutureValidityDate(),
                'CARDCVV'          => '123',
                'CARDFULLNAME'     => 'John Doe',
                'ORDERID'          => 'order_' . time(),
                'CLIENTIDENT'      => 'john.doe',
                'CLIENTEMAIL'      => 'john.doe42',
                'CLIENTEMAIL'      => 'john.doe@mail.com',
                'DESCRIPTION'      => 'Test',
                'CLIENTUSERAGENT'  => 'firefox',
                'CLIENTIP'         => '1.2.3.4',
                'VERSION'          => '2.0'
            ),
            array(
                'AMOUNT'           => 100,
                'OPERATIONTYPE'    => 'payment',
                'CARDCODE'         => '5555556778250000',
                'CARDVALIDITYDATE' => $this->tools->getFutureValidityDate(),
                'CARDCVV'          => '123',
                'CARDFULLNAME'     => 'John Doe',
                'ORDERID'          => 'order_' . time(),
                'CLIENTIDENT'      => 'john.doe',
                'CLIENTEMAIL'      => 'john.doe42',
                'CLIENTEMAIL'      => 'john.doe@mail.com',
                'DESCRIPTION'      => 'Test',
                'CLIENTUSERAGENT'  => 'firefox',
                'CLIENTIP'         => '1.2.3.4',
                'VERSION'          => '2.0'
            ),
        );

        $inputFile->fputcsv(array_keys(current($csv)));

        foreach ($csv as $line) {
            $inputFile->fputcsv($line);
        }

        $inputFile->rewind();

        $batchApi = Be2bill_Api_ClientBuilder::buildSandboxBatchClient($this->getIdentifier(), $this->getPassword());
        $batchApi->setInputFile($inputFile);

        $batchApi->attach(new Be2bill_Api_Batch_Observer_Debug());
        $batchApi->attach(new Be2bill_Api_Batch_Observer_FileReport($outputFile));

        $batchApi->run();

        $outputFile->rewind();

        for ($i = 0; !$outputFile->eof(); $i++) {
            $outputFile->fgetcsv();
        }

        $this->expectOutputRegex('/Line 1.+\nLine 2.+\nLine 3.+\nLine 4.+\n/');
        $this->assertEquals(5, $i);
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
