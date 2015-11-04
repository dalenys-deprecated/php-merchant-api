<?php

/**
 * Curl sender
 *
 * @package Be2bill
 * @subpackage Sender
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Send a HTTP request with curl
 */
class Be2bill_Api_Sender_Curl implements Be2bill_Api_Sender_Sendable
{
    const CURLE_OPERATION_TIMEDOUT = 28;
    const REQUEST_TIMEOUT = 30;

    /**
     * The timeout in seconds
     *
     * @var int
     */
    protected $timeout = self::REQUEST_TIMEOUT;

    /**
     * Indicate if should retry depending on failing cases
     *
     * @var bool
     */
    protected $shouldRetry = false;

    /**
     * Override the default timeout
     *
     * @param $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Send the HTTP request with curl
     *
     * @param string $url
     * @param array $params
     * @return boolean|string
     */
    public function send($url, array $params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        if ($result === false) {
            trigger_error('Unable to process request: ' . curl_error($ch), E_USER_WARNING);

            //Retry for all cases but timeout
            if (curl_errno($ch) == self::CURLE_OPERATION_TIMEDOUT) {
                $this->shouldRetry = false;
            } else {
                $this->shouldRetry = true;
            }
        }

        curl_close($ch);

        return $result;
    }

    /**
     * Tells if the request should be retried on a different URL if a previous send failed
     *
     * @return boolean
     */
    public function shouldRetry()
    {
        return $this->shouldRetry;
    }
}
