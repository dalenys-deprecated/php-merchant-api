<?php

class Be2bill_Api_Sender_Curl implements Be2bill_Api_Sender_Sendable
{
    const CURLE_OPERATION_TIMEDOUT = 28;

    const REQUEST_TIMEOUT = 30;

    protected $curlHandler = null;
    protected $timeout = null;
    protected $shouldRetry = false;

    public function __construct()
    {
        $this->curlHandler = curl_init();
        $this->timeout = self::REQUEST_TIMEOUT;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function send($url, array $params)
    {
        curl_setopt($this->curlHandler, CURLOPT_URL, $url);
        curl_setopt($this->curlHandler, CURLOPT_POST, true);
        curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($this->curlHandler, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($this->curlHandler);

        if ($result === false) {
            trigger_error(E_USER_WARNING, 'Unable to process request: ' . curl_error($this->curlHandler));

            //Retry for all cases but timeout
            if (curl_errno($this->curlHandler) == self::CURLE_OPERATION_TIMEDOUT) {
                $this->shouldRetry = false;
            } else {
                $this->shouldRetry = true;
            }
        }

        curl_close($this->curlHandler);

        return $result;
    }

    public function shouldRetry()
    {
        return $this->shouldRetry;
    }
}