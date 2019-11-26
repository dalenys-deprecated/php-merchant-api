<?php

/**
 * Hash interface
 *
 * @package Dalenys\Hash
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Interface for hashing
 */
interface Dalenys_Api_Hash_Hashable
{
    /**
     * Compute a hash
     *
     * @param string $password The secret key
     * @param array $params The parameters to compute
     * @return string The hashed string
     */
    public function compute($password, array $params = array());

    /**
     * Verify a HASH
     *
     * @param $password The secret key
     * @param array $params The parameters (should include a HASH parameter)
     * @return boolean
     */
    public function checkHash($password, array $params = array());
}
