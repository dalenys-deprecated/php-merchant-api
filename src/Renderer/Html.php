<?php

class Be2bill_Api_Renderer_Html implements Be2bill_Api_Renderer_Renderable
{
    protected $url = null;
    protected $encoding = 'UTF-8';
    protected $formPath = '/front/form/process';

    /**
     * @param $url The URL for payment form to submit
     */
    public function __construct($url)
    {
        $this->url = $url . $this->formPath;
    }

    /**
     * @param       $params
     * @param array $options
     * @return string
     */
    public function render($params, $options = array())
    {
        return $this->buildFormHtml($params, $options);
    }

    /**
     * @param       $params
     * @param array $htmlOptions
     * @return string
     */
    protected function buildFormHtml($params, $htmlOptions = array())
    {
        if (isset($htmlOptions['FORM'])) {
            $attributes = $this->buildAttributes($htmlOptions['FORM']);
        } else {
            $attributes = '';
        }

        // Return something like $prodUrl / formPath
        $html = '<form method="post" action="' . $this->url . '" '. $attributes .'>';

        $html .= $this->buildHiddenInputs($params);

        $html .= $this->buildSubmit($htmlOptions);
        $html .= '</form>';

        return $html;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @param $fields
     * @return string
     */
    protected function buildHiddenInputs($fields)
    {
        $html = '';
        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    // Build input like :<input type="hidden" name="AMOUNTS[XXXX-YY-ZZ]" value="100" />
                    $html .= $this->buildHiddenInput($key . '[' . $subKey . ']', $subValue);
                }
            } else {
                $html .= $this->buildHiddenInput($key, $value);
            }
        }

        return $html;
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    protected function buildHiddenInput($key, $value)
    {
        return '<input type="hidden" name="' . $this->escape($key) . '" value="' . $this->escape($value) . '">';
    }

    /**
     * @param $options
     * @return string
     */
    protected function buildSubmit($options)
    {
        if (isset($options['SUBMIT'])) {
            $attributes = $this->buildAttributes($options['SUBMIT']);
        } else {
            $attributes = '';
        }

        $html = '<input type="submit" ' . $attributes . ' />';

        return $html;
    }

    protected function buildAttributes(array $options = array())
    {
        $attributes = '';

        foreach ($options as $key => $value) {
            $attributes .= $this->escape($key) . '="' . $this->escape($value) . '" ';
        }

        return $attributes;
    }

    /**
     * @param $mixed
     * @return string
     */
    protected function escape($mixed)
    {
        return htmlentities($mixed, ENT_QUOTES, $this->encoding);
    }
}