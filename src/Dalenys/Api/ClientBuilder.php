<?php

/**
 * Client builder
 *
 * @package Dalenys
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Build API clients on different environments
 *
 * Usefull to simply create Dalenys_Api_Client object without managing dependencies
 */
abstract class Dalenys_Api_ClientBuilder
{
    /**
     * Production URLS
     *
     * @var array
     */
    protected static $productionUrls = array(
        'https://secure-magenta1.dalenys.com',
        'https://secure-magenta2.dalenys.com'
    );

    /**
     * Sandbox URLS
     *
     * @var array
     */
    protected static $sandboxUrls = array('https://secure-test.be2bill.com'); // TODO: change to dalenys

    /**
     * Build a production form builder
     *
     * @api
     * @param string $identifier
     * @param string $password
     * @return Dalenys_Api_FormClient
     */
    public static function buildProductionFormClient($identifier, $password)
    {
        $api = new Dalenys_Api_FormClient(
            $identifier,
            $password,
            new Dalenys_Api_Renderer_Html(current(self::$productionUrls)),
            new Dalenys_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Build a direclink production client
     *
     * @api
     * @param $identifier
     * @param $password
     * @return Dalenys_Api_DirectLinkClient
     */
    public static function buildProductionDirectLinkClient($identifier, $password)
    {
        $api = new Dalenys_Api_DirectLinkClient(
            $identifier,
            $password,
            self::$productionUrls,
            new Dalenys_Api_Sender_Curl(),
            new Dalenys_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Build a production batch client
     *
     * @api
     * @param $identifier
     * @param $password
     * @return Dalenys_Api_BatchClient
     */
    public static function buildProductionBatchClient($identifier, $password)
    {
        $api = self::buildProductionDirectLinkClient($identifier, $password);

        return new Dalenys_Api_BatchClient($api);
    }

    /**
     * Build a sandbox form builder (transactions are fake)
     *
     * @api
     * @param $identifier
     * @param $password
     * @return Dalenys_Api_FormClient
     */
    public static function buildSandboxFormClient($identifier, $password)
    {
        $api = new Dalenys_Api_FormClient(
            $identifier,
            $password,
            new Dalenys_Api_Renderer_Html(current(self::$sandboxUrls)),
            new Dalenys_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Build a sandbox directlink client (transactions are fake)
     *
     * @api
     * @param $identifier
     * @param $password
     * @return Dalenys_Api_DirectLinkClient
     */
    public static function buildSandboxDirectLinkClient($identifier, $password)
    {
        $api = new Dalenys_Api_DirectLinkClient(
            $identifier,
            $password,
            self::$sandboxUrls,
            new Dalenys_Api_Sender_Curl(),
            new Dalenys_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Build a sandbox batch client (transactions are fake)
     *
     * @api
     * @param $identifier
     * @param $password
     * @return Dalenys_Api_BatchClient
     */
    public static function buildSandboxBatchClient($identifier, $password)
    {
        $api = self::buildSandboxDirectLinkClient($identifier, $password);

        return new Dalenys_Api_BatchClient($api);
    }

    /**
     * Use another production URL
     * @api
     */
    public static function switchProductionUrls()
    {
        self::$productionUrls = array_reverse(self::$productionUrls);
    }
}
