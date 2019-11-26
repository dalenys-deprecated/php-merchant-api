<?php

use PHPUnit\Framework\TestCase;

class BatchClientTest extends TestCase
{
    protected function setUp()
    {
        $this->tools = new Dalenys_Api_Test_Tools();
    }

    public function test5Transactions()
    {
        $inputFile = fopen('php://memory', 'w+');
        $outputFile = fopen('php://memory', 'w+');

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

        fputcsv($inputFile, array_keys(current($csv)), ';');

        foreach ($csv as $line) {
            fputcsv($inputFile, $line, ';');
        }

        rewind($inputFile);

        $batchApi = Dalenys_Api_ClientBuilder::buildSandboxBatchClient($this->getIdentifier(), $this->getPassword());
        $batchApi->setInputFile($inputFile);

        $batchApi->attach(new Dalenys_Api_Batch_Observer_Debug());
        $batchApi->attach(new Dalenys_Api_Batch_Observer_FileReport($outputFile));

        $batchApi->run();

        rewind($outputFile);

        $i = 0;
        while (!feof($outputFile)) {
            $line = fgetcsv($outputFile, null, ';');

            // HACK for phpunit version >= 5.2
            if ($line) {
                $i++;
            }
        }

        $this->expectOutputRegex('/Line 1.+\nLine 2.+\nLine 3.+\nLine 4.+\n/');
        $this->assertEquals(5, $i);
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
