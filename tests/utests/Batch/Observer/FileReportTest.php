<?php

use PHPUnit\Framework\TestCase;

class Batch_Observer_FileReportTest extends TestCase
{
    protected $subjectMock;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder('Dalenys_Api_BatchClient')
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

        $file = fopen('php://memory', 'w+');

        $debug = new Dalenys_Api_Batch_Observer_FileReport($file);
        $debug->update($this->subjectMock);

        rewind($file);

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

        $file = fopen('php://memory', 'w+');

        $debug = new Dalenys_Api_Batch_Observer_FileReport($file);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);

        rewind($file);

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

        $file = fopen('php://memory', 'w+');

        $debug = new Dalenys_Api_Batch_Observer_FileReport($file);

        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);

        rewind($file);

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

        $file = fopen('php://memory', 'w+');

        $debug = new Dalenys_Api_Batch_Observer_FileReport($file);

        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);
        $debug->update($this->subjectMock);

        rewind($file);

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

        $file = fopen('php://memory', 'w+');

        $debug = new Dalenys_Api_Batch_Observer_FileReport($file);
        $debug->update($this->subjectMock);

        rewind($file);

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

        $this->subjectMock->expects($this->any())
            ->method('getDelimiter')
            ->will($this->returnValue(';'));

        $this->subjectMock->expects($this->any())
            ->method('getEnclosure')
            ->will($this->returnValue('"'));
    }

    protected function readFile($file)
    {
        $content = '';
        while (!feof($file)) {
            $content .= fgets($file);
        }

        return $content;
    }
}
