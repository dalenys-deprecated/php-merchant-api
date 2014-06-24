<?php

class Renderer_HtmlTest extends PHPUnit_Framework_TestCase
{
    public function testBuildHiddenInput()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com/');

        $this->assertEquals(
            '<input type="hidden" name="foo" value="bar" />',
            $renderer->buildHiddenInput('foo', 'bar')
        );
    }

    public function testEscaping()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com/');

        $this->assertEquals(
            '<input type="hidden" name="foo" value="h&eacute;" />',
            $renderer->buildHiddenInput('foo', 'hé')
        );
    }

    public function testBuildHiddenInputs()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com/');

        $this->assertEquals(
            '<input type="hidden" name="foo" value="bar" /><input type="hidden" name="baz" value="boz" />',
            $renderer->buildHiddenInputs(array('foo' => 'bar', 'baz' => 'boz'))
        );
    }

    public function testBuildSubmitInput()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com');

        $this->assertEquals('<input type="submit"  />', $renderer->buildSubmitInput(array()));
    }

    public function testRender()
    {
        $renderer = new Be2bill_Api_Renderer_Html('http://test.com');

        $html = '<form method="post" action="http://test.com/front/form/process" target="t" >' .
        '<input type="hidden" name="foo" value="bar" /><input type="submit" name="biz"  /></form>';

        $this->assertEquals(
            $html,
            $renderer->render(
                array('foo' => 'bar'),
                array('FORM' => array('target' => 't'), "SUBMIT" => array('name' => 'biz'))
            )
        );
    }
}
