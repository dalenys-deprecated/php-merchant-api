<?php

class Be2bill_Api_Sender_Curl implements Be2bill_Api_Sender_Sendable
{
    const CURLE_OPERATION_TIMEDOUT = 28;

    const REQUEST_TIMEOUT = 30;

    protected $timeout = null;
    protected $shouldRetry = false;

    public function __construct()
    {
        $this->timeout = self::REQUEST_TIMEOUT;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

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
            trigger_error(E_USER_WARNING, 'Unable to process request: ' . curl_error($ch));

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

    public function shouldRetry()
    {
        return $this->shouldRetry;
    }
}
