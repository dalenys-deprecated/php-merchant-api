<?php

/**
 * Functional test tools
 *
 * @package Be2bill\Test
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Toolkit class for tests
 */
class Be2bill_Api_Test_Tools
{
    /**
     * Get a validity date in the future (whooow!)
     *
     * @return string MM-YY string
     */
    public function getFutureValidityDate()
    {
        return date('m-y', time() + 365 * 24 * 3600);
    }
}
