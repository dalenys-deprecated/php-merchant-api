<?php

/**
 * Interface Be2bill_Api_Renderer_Renderable
 */
interface Be2bill_Api_Renderer_Renderable
{
    /**
     * @param       $params
     * @param array $options
     * @return string
     */
    public function render($params, $options = array());
}
