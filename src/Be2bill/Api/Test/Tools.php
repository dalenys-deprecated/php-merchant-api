<?php

class Be2bill_Api_Test_Tools
{
    public function getFutureValidityDate()
    {
        return date('m-y', time() + 365 * 24 * 3600);
    }
}
