<?php

/**
 * Render interface
 *
 * @package Be2bill\Renderer
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Render a payment form
 */
interface Be2bill_Api_Renderer_Renderable
{
    /**
     * Display a payment form
     *
     * @param array $params
     * @param array $options
     * @return string
     */
    public function render(array $params, array $options = array());
}
