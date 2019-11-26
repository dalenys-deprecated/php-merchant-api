<?php

/**
 * HTML renderer
 *
 * @package Dalenys\Renderer
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Render a payment form in HTML
 */
class Dalenys_Api_Renderer_Html implements Dalenys_Api_Renderer_Renderable
{
    /**
     * @var string The form action URL
     */
    protected $url;

    /**
     * @var string The form charset encoding
     */
    protected $encoding = 'UTF-8';

    /**
     * @var string The form action path
     */
    protected $formPath = '/front/form/process';

    /**
     * Instanciate
     *
     * @param string $url The URL for payment form to submit
     */
    public function __construct($url)
    {
        $this->url = $url . $this->formPath;
    }

    /**
     * Render the HTML form
     *
     * @param       $params Transaction parameters
     * @param array $options Transaction options
     * @return string The HTML
     */
    public function render(array $params, array $options = array())
    {
        if (isset($options['FORM'])) {
            $attributes = $this->buildAttributes($options['FORM']);
        } else {
            $attributes = '';
        }

        // Return something like $prodUrl / formPath
        $html = '<form method="post" action="' . $this->url . '" ' . $attributes . '>';

        $html .= $this->buildHiddenInputs($params);
        $html .= $this->buildSubmitInput($options);

        $html .= '</form>';

        return $html;
    }

    /**
     * Render hidden inputs
     *
     * @param array $fields The input list to validate
     * @return string The HTML
     */
    public function buildHiddenInputs(array $fields)
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
     * Render 1 hidden input
     *
     * @param $key The input name
     * @param $value The input value
     * @return string the HTML
     */
    public function buildHiddenInput($key, $value)
    {
        return '<input type="hidden" name="' . $this->escape($key) . '" value="' . $this->escape($value) . '" />';
    }

    /**
     * Render the submit button
     *
     * @param array $options The submit options
     * @return string The HTML
     */
    public function buildSubmitInput(array $options = array())
    {
        if (isset($options['SUBMIT'])) {
            $attributes = $this->buildAttributes($options['SUBMIT']);
        } else {
            $attributes = '';
        }

        $html = '<input type="submit" ' . $attributes . ' />';

        return $html;
    }

    /**
     * Set form encoding
     *
     * @param $encoding The encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Render HTML attributes
     *
     * @param array $options The attributes to render
     * @return string The HTML
     */
    protected function buildAttributes(array $options = array())
    {
        $attributes = '';

        foreach ($options as $key => $value) {
            $attributes .= $this->escape($key) . '="' . $this->escape($value) . '" ';
        }

        return $attributes;
    }

    /**
     * Escape a variable against XSS
     *
     * @param string $mixed The variable
     * @return string The escaped value
     */
    protected function escape($mixed)
    {
        return htmlentities($mixed, ENT_QUOTES, $this->encoding);
    }
}
