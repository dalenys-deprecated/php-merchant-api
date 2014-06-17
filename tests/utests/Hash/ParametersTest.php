<?php

class Hash_ParametersTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleHash()
    {
        $hash = new Be2bill_Api_Hash_Parameters();
        $this->assertEquals(
            $hash->compute('password', array('c' => 3, 'a' => '1', 'b' => '2')),
            '77c71c1e70ea28525cf078537d22d1932922e3741ed83287b0dc0a117bf77999'
        );
    }

    public function testHashWithHashParameterInData()
    {
        $hash = new Be2bill_Api_Hash_Parameters();
        $this->assertEquals(
            $hash->compute('password', array('c' => 3, 'a' => '1', 'b' => '2', 'HASH' => 'shouldnotimpact')),
            '77c71c1e70ea28525cf078537d22d1932922e3741ed83287b0dc0a117bf77999'
        );
    }

    public function testHashWithSubData()
    {
        $hash = new Be2bill_Api_Hash_Parameters();
        $this->assertEquals(
            $hash->compute(
                'password',
                array(
                    'c' => 3,
                    'a' => '1',
                    'b' => '2',
                    'd' => array(
                        'y' => 43,
                        'x' => 42
                    )
                )
            ),
            '376383093261372eb97909ed1a44b1adb5e8f2687f7a64f1c41d5a0c8cc0b0fa'
        );
    }
}