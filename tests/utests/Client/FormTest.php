<?php

class Client_FormTest extends PHPUnit_Framework_TestCase
{
    protected $renderMock = null;
    protected $hashStub = null;

    /**
     * @var Be2bill_Api_FormClient
     */
    protected $api = null;

    public function setUp()
    {
        $this->renderMock  = $this->getMock('Be2bill_Api_Renderer_Renderable');
        $this->hashStub    = $this->getMock('Be2bill_Api_Hash_Hashable');

        $this->hashStub->expects($this->once())
            ->method('compute')
            ->will($this->returnValue('dummy'));

        $this->api = new Be2bill_Api_FormClient(
            'i',
            'p',
            $this->renderMock,
            $this->hashStub
        );
    }

    public function testFormPayment()
    {
        $this->renderMock->expects($this->once())
            ->method('render')
            ->with(
                array(
                    'AMOUNT'        => 100,
                    'IDENTIFIER'    => 'i',
                    'OPERATIONTYPE' => 'payment',
                    'ORDERID'       => 42,
                    'CLIENTIDENT'   => 'ident',
                    'VERSION'       => '2.0',
                    'HASH'          => 'dummy',
                    'DESCRIPTION'   => 'desc'
                )
            );

        $this->api->buildPaymentFormButton(100, 42, 'ident', 'desc');
    }

    public function testNTimeFormPayment()
    {
        $this->renderMock->expects($this->once())
            ->method('render')
            ->with(
                array(
                    'AMOUNTS'       => array('2014-01-16' => 100, '2014-02-16' => 100, '2014-03-16' => 100),
                    'IDENTIFIER'    => 'i',
                    'OPERATIONTYPE' => 'payment',
                    'ORDERID'       => 42,
                    'CLIENTIDENT'   => 'ident',
                    'VERSION'       => '2.0',
                    'HASH'          => 'dummy',
                    'DESCRIPTION'   => 'desc'
                )
            );

        $this->api->buildPaymentFormButton(
            array('2014-01-16' => 100, '2014-02-16' => 100, '2014-03-16' => 100),
            42,
            'ident',
            'desc'
        );
    }

    public function testFormAuthorization()
    {
        $this->renderMock->expects($this->once())
            ->method('render')
            ->with(
                array(
                    'AMOUNT'        => 100,
                    'IDENTIFIER'    => 'i',
                    'OPERATIONTYPE' => 'authorization',
                    'ORDERID'       => 42,
                    'CLIENTIDENT'   => 'ident',
                    'VERSION'       => '2.0',
                    'HASH'          => 'dummy',
                    'DESCRIPTION'   => 'desc'
                )
            );

        $this->api->buildAuthorizationFormButton(100, 42, 'ident', 'desc');
    }

    public function testVersionOverloading()
    {
        $this->renderMock->expects($this->once())
            ->method('render')
            ->with(
                array(
                    'AMOUNT'        => 100,
                    'IDENTIFIER'    => 'i',
                    'OPERATIONTYPE' => 'payment',
                    'ORDERID'       => 42,
                    'CLIENTIDENT'   => 'ident',
                    'VERSION'       => '3.0',
                    'HASH'          => 'dummy',
                    'DESCRIPTION'   => 'desc'
                )
            );

        $this->api->buildPaymentFormButton(100, 42, 'ident', 'desc', array(), array('VERSION' => '3.0'));
    }

    public function testSetDefaultVersion()
    {
        $this->api->setVersion('3.0');

        $this->renderMock->expects($this->once())
            ->method('render')
            ->with(
                array(
                    'AMOUNT'        => 100,
                    'IDENTIFIER'    => 'i',
                    'OPERATIONTYPE' => 'payment',
                    'ORDERID'       => 42,
                    'CLIENTIDENT'   => 'ident',
                    'VERSION'       => '3.0',
                    'HASH'          => 'dummy',
                    'DESCRIPTION'   => 'desc'
                )
            );

        $this->api->buildPaymentFormButton(100, 42, 'ident', 'desc', array());
    }
}
