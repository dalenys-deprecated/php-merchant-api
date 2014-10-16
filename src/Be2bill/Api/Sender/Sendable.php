<?php

interface Be2bill_Api_Sender_Sendable
{
    public function send($url, array $params);
    public function shouldRetry();
}
