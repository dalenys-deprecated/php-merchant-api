<?php

interface Be2bill_Api_Hash_Hashable
{
    public function compute($password, array $params = array());

    public function checkHash($password, array $params = array());
}
