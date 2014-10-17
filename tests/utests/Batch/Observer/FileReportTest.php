<?php

class Batch_Observer_FileReportTest extends PHPUnit_Framework_TestCase
{
    protected $subjectMock;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder('Be2bill_Api_BatchClient')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testFirstTransaction()
    {
        $this->subjectMockScenario(
            array(
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                )
            ),
            array(
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1234'
                )
            )
        );

        $file = new SplTempFileObject();
        $file->setCsvControl(';');

        $debug = new Be2bill_Api_Batch_Observer_FileReport($file);
        $debug->update($this->subjectMock);

        $file->rewind();

        $expected = <<<RESULT
AMOUNT;EXECCODE;MESSAGE;ORDERID;TRANSACTIONID
100;0000;"Operation succeeded";42;A1234

RESULT;

        $content = $this->readFile($file);
        $this->assertEquals($expected, $content);
    }

    public function test3TransactionsWithAMissingResultField()
    {
        $this->subjectMockScenario(
            array(
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                )
            ),
            array(
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1234'
                ),
                array(
                    'EXECCODE' => '1001',
                    'MESSAGE'  => 'Parameter missing',
                ),
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1235'
                )
            )
        );

        $file = new SplTempFileObject();
        $file->setCsvControl(';');

        $debug = new Be2bill_Api_Batch_Observer_FileReport($file);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);

        $file->rewind();

        $result = <<<RESULT
AMOUNT;EXECCODE;MESSAGE;ORDERID;TRANSACTIONID
100;0000;"Operation succeeded";42;A1234
100;1001;"Parameter missing";42;
100;0000;"Operation succeeded";42;A1235

RESULT;

        $content = $this->readFile($file);
        $this->assertEquals($result, $content);
    }

    public function test5TransactionsWithANewResultField()
    {
        $this->subjectMockScenario(
            array(
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                )
            ),
            array(
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1234'
                ),
                array(
                    'EXECCODE' => '1001',
                    'MESSAGE'  => 'Parameter missing',
                ),
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1235'
                ),
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1235',
                    'NEWFIELD'      => 'NEW'
                ),
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1235'
                )
            )
        );

        $file = new SplTempFileObject();
        $file->setCsvControl(';');

        $debug = new Be2bill_Api_Batch_Observer_FileReport($file);

        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);

        $file->rewind();

        $result = <<<RESULT
AMOUNT;EXECCODE;MESSAGE;NEWFIELD;ORDERID;TRANSACTIONID
100;0000;"Operation succeeded";;42;A1234
100;1001;"Parameter missing";;42;
100;0000;"Operation succeeded";;42;A1235
100;0000;"Operation succeeded";NEW;42;A1235
100;0000;"Operation succeeded";;42;A1235

RESULT;

        $content = $this->readFile($file);
        $this->assertEquals($result, $content);
    }

    public function test5TransactionsWithANewResultAndChangingFieldsOrder()
    {
        $this->subjectMockScenario(
            array(
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                ),
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                )
            ),
            array(
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1234'
                ),
                array(
                    'EXECCODE' => '1001',
                    'MESSAGE'  => 'Parameter missing',
                ),
                array(
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1235',
                    'EXECCODE'      => '0000'
                ),
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1235',
                    'NEWFIELD'      => 'NEW'
                ),
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1235'
                )
            )
        );

        $file = new SplTempFileObject();
        $file->setCsvControl(';');

        $debug = new Be2bill_Api_Batch_Observer_FileReport($file);

        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);

        $file->rewind();

        $result = <<<RESULT
AMOUNT;EXECCODE;MESSAGE;NEWFIELD;ORDERID;TRANSACTIONID
100;0000;"Operation succeeded";;42;A1234
100;1001;"Parameter missing";;42;
100;0000;"Operation succeeded";;42;A1235
100;0000;"Operation succeeded";NEW;42;A1235
100;0000;"Operation succeeded";;42;A1235

RESULT;

        $content = $this->readFile($file);
        $this->assertEquals($result, $content);
    }

    public function testSameParameterInParamAndResult()
    {
        $this->subjectMockScenario(
            array(
                array(
                    'ORDERID' => '42',
                    'AMOUNT'  => 100,
                )
            ),
            array(
                array(
                    'EXECCODE'      => '0000',
                    'MESSAGE'       => 'Operation succeeded',
                    'TRANSACTIONID' => 'A1234',
                    'ORDERID'       => 42,
                )
            )
        );

        $file = new SplTempFileObject();
        $file->setCsvControl(';');

        $debug = new Be2bill_Api_Batch_Observer_FileReport($file);
        $debug->update($this->subjectMock);

        $file->rewind();

        $expected = <<<RESULT
AMOUNT;EXECCODE;MESSAGE;ORDERID;TRANSACTIONID
100;0000;"Operation succeeded";42;A1234

RESULT;

        $content = $this->readFile($file);
        $this->assertEquals($expected, $content);
    }

    protected function subjectMockScenario(array $params, array $results)
    {
        $nbCalls = sizeof($params);

        $this->subjectMock->expects($this->exactly($nbCalls))
            ->method('getCurrentTransactionParameters')
            ->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $params));

        $this->subjectMock->expects($this->exactly($nbCalls))
            ->method('getCurrentTransactionResult')
            ->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $results));

        $this->subjectMock->expects($this->exactly($nbCalls))
            ->method('getCurrentLine')
            ->will(call_user_func_array(array($this, 'onConsecutiveCalls'), range(0, $nbCalls)));
    }

    protected function readFile($file)
    {
        $content = '';
        while (!$file->eof()) {
            $content .= $file->fgets();
        }
        return $content;
    }
}
