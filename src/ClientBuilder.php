<?php

/**
 * Class Be2bill_Api_ClientBuilder
 * Usefull to simply create Be2bill_Api_Client object without managing dependencies
 */
abstract class Be2bill_Api_ClientBuilder
{
    protected static $productionUrls = array('https://secure-magenta1.be2bill.com', 'https://secure-magenta2.be2bill.com');
    protected static $sandboxUrls = array('https://secure-test.be2bill.com');

    /**
     * Build a production client
     * @param $identifier
     * @param $password
     * @param $urls
     * @return Be2bill_Api_Client
     */
    public static function buildProductionClient($identifier, $password)
    {
        $api = new Be2bill_Api_Client(
            $identifier, $password, self::$productionUrls,
            new Be2bill_Api_Renderer_Html(current(self::$productionUrls)),
            new Be2bill_Api_Sender_Curl(),
            new Be2bill_Api_Hash_Parameters()
        );

        return $api;
    }

    /**
     * Build a sandbox client (transactions are fake)
     * @param $identifier
     * @param $password
     * @param $urls
     * @return Be2bill_Api_Client
     */
    public static function buildSandboxClient($identifier, $password)
    {
        $api = new Be2bill_Api_Client(
            $identifier, $password, self::$sandboxUrls,
            new Be2bill_Api_Renderer_Html(current(self::$sandboxUrls)),
            new Be2bill_Api_Sender_Curl(),
            new Be2bill_Api_Hash_Parameters()
        );

        return $api;
    }

    public static function switchUrls()
    {
        self::$productionUrls = array_reverse(self::$productionUrls);
    }
}