<?php

class Renderer_HtmlTest extends PHPUnit_Framework_TestCase
{
    public function testBuildHiddenInput()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com/');

        $reflected = new ReflectionClass('Be2bill_Api_Renderer_Html');
        $method    = $reflected->getMethod('buildHiddenInput');
        $method->setAccessible(true);

        $this->assertEquals(
            '<input type="hidden" name="foo" value="bar" />',
            $method->invokeArgs($renderer, array('foo', 'bar'))
        );
    }

    public function testEscaping()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com/');

        $reflected = new ReflectionClass('Be2bill_Api_Renderer_Html');
        $method    = $reflected->getMethod('buildHiddenInput');
        $method->setAccessible(true);

        $this->assertEquals(
            '<input type="hidden" name="foo" value="h&eacute;" />',
            $method->invokeArgs($renderer, array('foo', 'hé'))
        );
    }

    public function testBuildHiddenInputs()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com/');

        $reflected = new ReflectionClass('Be2bill_Api_Renderer_Html');
        $method    = $reflected->getMethod('buildHiddenInputs');
        $method->setAccessible(true);

        $this->assertEquals(
            '<input type="hidden" name="foo" value="bar" /><input type="hidden" name="baz" value="boz" />',
            $method->invokeArgs($renderer, array(array('foo' => 'bar', 'baz' => 'boz')))
        );
    }

    public function testBuildSubmitInput()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com');

        $reflected = new ReflectionClass('Be2bill_Api_Renderer_Html');
        $method    = $reflected->getMethod('buildSubmitInput');
        $method->setAccessible(true);

        $this->assertEquals('<input type="submit"  />', $method->invokeArgs($renderer, array(array())));
    }

    public function testRender()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com');

        $html = <<<HTML
<form method="post" action="http://test.com/front/form/process" target="t" ><input type="hidden" name="foo" value="bar" /><input type="submit" name="biz"  /></form>
HTML;

        $this->assertEquals(
            $html,
            $renderer->render(
                array('foo' => 'bar'),
                array('FORM' => array('target' => 't'), "SUBMIT" => array('name' => 'biz'))
            )
        );
    }
}