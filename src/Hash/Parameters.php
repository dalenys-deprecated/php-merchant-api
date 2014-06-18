<?php

class Be2bill_Api_Hash_Parameters implements Be2bill_Api_Hash_Hashable
{
    /**
     * Compute a HASH from an array
     * @param       $password
     * @param array $data
     * @return string
     */
    public function compute($password, array $data = array())
    {
        $clear_string = $password;

        ksort($data);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                ksort($value);
                foreach ($value as $index => $val) {
                    $clear_string .= $key . '[' . $index . ']=' . $val . $password;
                }
            } else {
                if ($key == 'HASH') {
                    // Skip HASH parameter if supplied
                    continue;
                } else {
                    $clear_string .= $key . '=' . $value . $password;
                }
            }
        }

        return hash('sha256', $clear_string);
    }

    /**
     * Verify an array containing a HASH parameter
     * @param       $password
     * @param array $params
     */
    public function checkHash($password, array $params = array())
    {
        $received_hash   = $params['HASH'];
        $calculated_hash = $this->compute($password, $params);

        return $received_hash == $calculated_hash;
    }
}
