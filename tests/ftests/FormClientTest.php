<?php

class FormClient_FormTest extends PHPUnit_Framework_TestCase
{
    public function testPaymentFormDisplay()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxFormClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $html = $api->buildPaymentFormButton('1000', 'order-' . time(), 'ident', 'desc');

        $inputs = $this->getInputsFromHtml($html);
        $action = $this->getFormActionFromHtml($html);

        $result = $this->request($action, $inputs);

        $inputs = $this->getInputsFromHtml($result);

        $this->assertArrayHasKey('IDENTIFIER', $inputs);
    }

    public function testAuthorizationFormDisplay()
    {
        $api = Be2bill_Api_ClientBuilder::buildSandboxFormClient(BE2BILL_TEST_IDENTIFIER, BE2BILL_TEST_PASSWORD);

        $html = $api->buildAuthorizationFormButton('1000', 'order-' . time(), 'ident', 'desc');

        $inputs = $this->getInputsFromHtml($html);
        $action = $this->getFormActionFromHtml($html);

        $result = $this->request($action, $inputs);

        $inputs = $this->getInputsFromHtml($result);

        $this->assertArrayHasKey('IDENTIFIER', $inputs);
    }

    protected function getInputsFromHtml($html)
    {
        $xml = simplexml_load_string('<html>' . $html . '</html>');

        $params = array();
        foreach ($xml->xpath("//input[@name][@value]") as $elm) {
            $attributes                          = $elm->attributes();
            $params[(string)$attributes['name']] = (string)$attributes['value'];
        }

        return $params;
    }

    protected function getFormActionFromHtml($html)
    {
        $xml = simplexml_load_string('<html>' . $html . '</html>');

        $form = current($xml->xpath('//form'));
        $form = $form->attributes();

        return (string)$form['action'];
    }

    protected function request($url, $params)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        return curl_exec($ch);
    }
}
