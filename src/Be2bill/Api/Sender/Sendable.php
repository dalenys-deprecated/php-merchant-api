<?php

/**
 * Sender interface
 *
 * @package Be2bill\Sender
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Interface for sending be2bill requests
 */
interface Be2bill_Api_Sender_Sendable
{
    /**
     * Send a request
     *
     * @param string $url Url to sent the request
     * @param array $params POST params
     * @return string The request result
     */
    public function send($url, array $params);

    /**
     * Tells if the request should be retried on a different URL if a previous send failed
     *
     * @return boolean
     */
    public function shouldRetry();
}
