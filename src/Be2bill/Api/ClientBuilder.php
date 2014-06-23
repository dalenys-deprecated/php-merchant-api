<?php

/**
 * Class Be2bill_Api_ClientBuilder
 * Usefull to simply create Be2bill_Api_Client object without managing dependencies
 */
abstract class Be2bill_Api_ClientBuilder
{
    protected static $productionUrls = array(
        'https://secure-magenta1.be2bill.com',
        'https://secure-magenta2.be2bill.com'
    );
    protected static $sandboxUrls = array('https://secure-test.be2bill.com');

    /**
     * Build a production form builder
     * @param $identifier
     * @param $password
     * @param $urls
     * @return Be2bill_Api_FormClient
     */
    public static function buildProductionFormClient($identifier, $password)
    {
        $api = new Be2bill_Api_FormClient(
            $identifier,
            $password,
            new Be2bill_Api_Renderer_Html(current(self::$productionUrls)),
            new Be2bill_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Build a direclink production client
     * @param $identifier
     * @param $password
     * @return Be2bill_Api_DirectLinkClient
     */
    public static function buildProductionDirectLinkClient($identifier, $password)
    {
        $api = new Be2bill_Api_DirectLinkClient(
            $identifier,
            $password,
            self::$productionUrls,
            new Be2bill_Api_Sender_Curl(),
            new Be2bill_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Build a sandbox form builder (transactions are fake)
     * @param $identifier
     * @param $password
     * @param $urls
     * @return Be2bill_Api_FormClient
     */
    public static function buildSandboxFormClient($identifier, $password)
    {
        $api = new Be2bill_Api_FormClient(
            $identifier,
            $password,
            new Be2bill_Api_Renderer_Html(current(self::$sandboxUrls)),
            new Be2bill_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Build a sandbox directlink client
     * @param $identifier
     * @param $password
     * @return Be2bill_Api_DirectLinkClient
     */
    public static function buildSandboxDirectLinkClient($identifier, $password)
    {
        $api = new Be2bill_Api_DirectLinkClient(
            $identifier,
            $password,
            self::$sandboxUrls,
            new Be2bill_Api_Sender_Curl(),
            new Be2bill_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Use another production URL
     */
    public static function switchProductionUrls()
    {
        self::$productionUrls = array_reverse(self::$productionUrls);
    }
}
