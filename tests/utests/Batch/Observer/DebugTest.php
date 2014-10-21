<?php

class Batch_Observer_DebugTest extends PHPUnit_Framework_TestCase
{
    protected $subjectMock;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder('Be2bill_Api_BatchClient')
            ->disableOriginalConstructor()
            ->getMock();

        $this->subjectMock->expects($this->once())
            ->method('getCurrentLine')
            ->will(
                $this->returnValue(0)
            );

        $this->subjectMock->expects($this->once())
            ->method('getCurrentTransactionParameters')
            ->will(
                $this->returnValue(
                    array(
                        'ORDERID' => '42',
                    )
                )
            );
    }

    public function testLogSucceedTransaction()
    {
        $this->subjectMock->expects($this->once())
            ->method('getCurrentTransactionResult')
            ->will(
                $this->returnValue(
                    array(
                        'EXECCODE'      => '0000',
                        'MESSAGE'       => 'Operation succeeded',
                        'TRANSACTIONID' => 'A1234'
                    )
                )
            );

        $this->expectOutputString(
            "Line 1 (ORDERID=42) : EXECCODE=0000 MESSAGE=Operation succeeded TRANSACTIONID=A1234\n"
        );

        $debug = new Be2bill_Api_Batch_Observer_Debug();
        $debug->update($this->subjectMock);
    }

    public function testLogFailedTransactionWithoutTransactionId()
    {
        $this->subjectMock->expects($this->once())
            ->method('getCurrentTransactionResult')
            ->will(
                $this->returnValue(
                    array(
                        'EXECCODE' => '1012',
                        'MESSAGE'  => 'Missing parameter',
                    )
                )
            );

        $this->expectOutputString("Line 1 (ORDERID=42) : EXECCODE=1012 MESSAGE=Missing parameter\n");

        $debug = new Be2bill_Api_Batch_Observer_Debug();
        $debug->update($this->subjectMock);
    }
}
