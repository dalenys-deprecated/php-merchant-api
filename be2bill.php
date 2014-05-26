<?php

/**
 * Implements Be2bill payment API
 * @version 1.0.0
 */
class be2bill
{
    const API_VERSION     = '2.0';
    const REQUEST_TIMEOUT = 30;

    // API urls
    protected $_urls = array('https://secure-magenta1.be2bill.com', 'https://secure-magenta2.be2bill.com');

    // Path
    protected $_formPath = '/front/form/process';
    protected $_directlinkPath = '/front/service/rest/process';
    protected $_exportPath = '/front/service/rest/export';
    protected $_reconcilPath = '/front/service/rest/reconciliation';

    // Credentials
    protected $_identifier = null;
    protected $_password = null;

    protected $_encoding = 'UTF-8';

    public function __construct($identifier, $password)
    {
        $this->setCredentials($identifier, $password);
    }

    /**
     * Build form payment and submit button
     *
     * This method will return the form payment and all hidden input configuring the be2bill transaction.
     * @param integer|array $amount The transaction amount in cents.
     *  If $amount is an array it will be used as NTime transaction (fragmented payment).
     *  In this case, the array should be formatted this way:
     *  <code>
     *      $amounts = array('2014-01-01' => 100, '2014-02-01' => 200, '2014-03-01' => 100)
     *  </code>
     *  The first entry's date should be the current date (today)
     * @param string $clientIdentifier
     * @param string $description
     * @param array $htmlOptions An array of HTML attributes to add to the submit button (allowing to change name, style, class attribute etc.)
     * @param array $options Others be2bill options. See Be2bill documentation for more information (3DS, CREATEALIAS, etc.)
     * @return string The HTML output to display
     */
    public function buildPaymentFormButton($amount, $clientIdentifier, $description, $htmlOptions = array(), $options = array())
    {
        $params = $options;

        // Handle N-Time payments
        if (is_array($amount))
        {
            $params["AMOUNTS"] = $amount;
        }
        else
        {
            $params["AMOUNT"] = $amount;
        }

        return $this->_buildFormButton('payment', $clientIdentifier, $description, $htmlOptions, $params);
    }

    /**
     * Build form authorization and submit button
     *
     * This method will return the form authorization and all hidden input configuring the be2bill transaction.
     * You will have to call the {@link capture} method to confirm this authorization
     * @param int $amount
     * @param string $clientIdentifier
     * @param string $description
     * @param string array $htmlOptions An array of HTML attributes to add to the submit button (allowing to change name, style, class attribute etc.)
     * @param string array $options Others be2bill options. See Be2bill documentation for more information (3DS, CREATEALIAS etc.)
     * @see capture
     * @return string The HTML output to display
     */
    public function buildAuthorizationFormButton($amount, $clientIdentifier, $description, $htmlOptions = array(), $options = array())
    {
        $params = $options;

        $params["AMOUNT"] = $amount;

        return $this->_buildFormButton('authorization', $clientIdentifier, $description, $htmlOptions, $params);
    }

    // Editing API

    /**
     * This method is used to stop a NTime transaction and will return the result formatted as an array.
     *
     * @param $scheduleId
     * @param array $options
     * @return bool|string
     */
    public function stopnTimes($scheduleId, $options = array())
    {
        $params = $options;

        $params['IDENTIFIER']    = $this->_identifier;
        $params['OPERATIONTYPE'] = 'stopntimes';
        $params['SCHEDULEID'] = $scheduleId;
        $params['VERSION']       = self::API_VERSION;

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_directlinkPath), $params);
    }

    /**
     * This method is used to refund a transaction and will return the result formatted as an array.
     *
     * @param $transactionId
     * @param $description
     * @param array $options
     * @return bool|mixed
     */
    public function refund($transactionId, $description, $options = array())
    {
        $params = $options;

        $params['IDENTIFIER']    = $this->_identifier;
        $params['OPERATIONTYPE'] = 'refund';
        $params['DESCRIPTION']   = $description;
        $params['TRANSACTIONID'] = $transactionId;
        $params['VERSION']       = self::API_VERSION;

        if (!isset($options['ORDERID']))
        {
            $params['ORDERID'] = uniqid('refd_');
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_directlinkPath), $params);
    }

    /**
     * This method is used to capture an authorization and will return the result formatted as an array.
     *
     * @param $transactionId
     * @param $description
     * @param array $options
     * @return bool|mixed
     */
    public function capture($transactionId, $description, $options = array())
    {
        $params = $options;

        $params['IDENTIFIER']    = $this->_identifier;
        $params['OPERATIONTYPE'] = 'capture';
        $params['VERSION']       = self::API_VERSION;
        $params['DESCRIPTION']   = $description;
        $params['TRANSACTIONID'] = $transactionId;

        if (!isset($options['ORDERID']))
        {
            $params['ORDERID'] = uniqid('cap_');
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_directlinkPath), $params);
    }

    /**
     * This method is used to initiate a oneClick transaction using an ALIAS and will return the result formatted as an array.
     *
     * @param $alias
     * @param $amount
     * @param $clientIdentifier
     * @param $clientEmail
     * @param $description
     * @param $clientIP
     * @param $clientUserAgent
     * @param array $options
     * @return bool|mixed
     */
    public function oneClick($alias, $amount, $clientIdentifier, $clientEmail, $description, $clientIP, $clientUserAgent, $options = array())
    {
        $params = $options;

        $params['CLIENTIDENT']     = $this->_escape($clientIdentifier);
        $params['CLIENTEMAIL']     = $this->_escape($clientEmail);
        $params['DESCRIPTION']     = $this->_escape($description);
        $params['CLIENTUSERAGENT'] = $this->_escape($clientUserAgent);
        $params['CLIENTIP']        = $this->_escape($clientIP);
        $params['ALIAS']           = $this->_escape($alias);
        $params['ALIASMODE']       = 'oneclick';
        $params['IDENTIFIER']      = $this->_identifier;
        $params['VERSION']         = self::API_VERSION;

        if (is_array($amount))
        {
            $params["AMOUNTS"] = $amount;
        }
        else
        {
            $params["AMOUNT"] = $amount;
        }

        if (!isset($options['OPERATIONTYPE']))
        {
            $params['OPERATIONTYPE'] = 'payment';
        }

        if (!isset($options['ORDERID']))
        {
            $params['ORDERID'] = uniqid('1clk_');
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_directlinkPath), $params);
    }

    /**
     * This method is used to initiate a recurring transaction using an ALIAS and will return the result formatted as an array.
     *
     * @param $alias
     * @param $amount
     * @param $clientIdentifier
     * @param $clientEmail
     * @param $description
     * @param $clientIP
     * @param $clientUserAgent
     * @param array $options
     * @return bool|mixed
     */
    public function subscription($alias, $amount, $clientIdentifier, $clientEmail, $description, $clientIP, $clientUserAgent, $options = array())
    {
        $params = $options;

        $params["AMOUNT"]          = $amount;
        $params['CLIENTIDENT']     = $this->_escape($clientIdentifier);
        $params['CLIENTEMAIL']     = $this->_escape($clientEmail);
        $params['DESCRIPTION']     = $this->_escape($description);
        $params['CLIENTUSERAGENT'] = $this->_escape($clientUserAgent);
        $params['CLIENTIP']        = $this->_escape($clientIP);
        $params['ALIAS']           = $this->_escape($alias);
        $params['ALIASMODE']       = 'subscription';
        $params['IDENTIFIER']      = $this->_identifier;
        $params['VERSION']         = self::API_VERSION;

        if (!isset($options['OPERATIONTYPE']))
        {
            $params['OPERATIONTYPE'] = 'payment';
        }

        if (!isset($options['ORDERID']))
        {
            $params['ORDERID'] = uniqid('sub_');
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_directlinkPath), $params);
    }

    // Wallets

    /**
     * This method is used to redirect a cardholder to an alternative payment provider and will return the result formatted as an array.
     *
     * @param $amount
     * @param $clientIdentifier
     * @param $clientEmail
     * @param $description
     * @param $clientIP
     * @param $clientUserAgent
     * @param array $options
     * @return bool|string
     */
    public function walletRedirection($amount, $clientIdentifier, $clientEmail, $description, $clientIP, $clientUserAgent, $options = array())
    {
        $params = $options;

        $params["AMOUNT"]          = $amount;
        $params['CLIENTIDENT']     = $this->_escape($clientIdentifier);
        $params['CLIENTEMAIL']     = $this->_escape($clientEmail);
        $params['DESCRIPTION']     = $this->_escape($description);
        $params['CLIENTUSERAGENT'] = $this->_escape($clientUserAgent);
        $params['CLIENTIP']        = $this->_escape($clientIP);
        $params['IDENTIFIER']      = $this->_identifier;
        $params['VERSION']         = self::API_VERSION;

        if (!isset($options['OPERATIONTYPE']))
        {
            $params['OPERATIONTYPE'] = 'payment';
        }

        if (!isset($options['ORDERID']))
        {
            $params['ORDERID'] = uniqid('wllt_');
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_directlinkPath), $params);
    }

    // Export methods

    /**
     * This method is used to recover one or several transactions using the ORDERID.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param $orderId
     * @param $to
     * @param string $compression
     * @return bool|mixed
     */
    public function getTransactionsByOrderId($orderId, $to, $compression = 'GZIP')
    {
        return $this->_getTransactions('ORDERID', $orderId, $to, $compression);
    }

    /**
     * This method is used to recover one or several transactions using the TRANSACTIONID.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param $transactionId
     * @param $to
     * @param string $compression
     * @return bool|mixed
     */
    public function getTransactionsByTransactionId($transactionId, $to, $compression = 'GZIP')
    {
        return $this->_getTransactions('TRANSACTIONID', $transactionId, $to, $compression);
    }

    /**
     * This method is used to recover the list of transactions for a given day or month.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param $date
     * @param string $timeZone
     * @param $to
     * @param string $compression
     * @return bool|mixed
     */
    public function exportTransactions($date, $to, $compression = 'GZIP', $options = array())
    {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["DATE"]          = $date;
        $params["OPERATIONTYPE"] = 'exportTransactions';
        $params['IDENTIFIER']    = $this->_identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->_isHttpUrl($to))
        {
            $params['CALLBACKURL'] = $to;
        }
        else
        {
            $params['MAILTO'] = $to;
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_exportPath), $params);
    }

    /**
     * This method is used to recover the list of chargebacks for a given day or month.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param $date
     * @param string $timeZone
     * @param $to
     * @param $compression
     * @return bool|mixed
     */
    public function exportChargebacks($date, $to, $compression = 'GZIP', $options = array())
    {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["OPERATIONTYPE"] = 'exportChargebacks';
        $params["DATE"]          = $date;
        $params['IDENTIFIER']    = $this->_identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->_isHttpUrl($to))
        {
            $params['CALLBACKURL'] = $to;
        }
        else
        {
            $params['MAILTO'] = $to;
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_exportPath), $params);
    }

    /**
     * This method is used to recover the reconciliations for a given month.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param $date
     * @param string $timeZone
     * @param $to
     * @param $compression
     * @return bool|mixed
     */
    public function exportReconciliations($date, $to, $compression = 'GZIP', $options = array())
    {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["OPERATIONTYPE"] = 'export';
        $params["DATE"]          = $date;
        $params['IDENTIFIER']    = $this->_identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->_isHttpUrl($to))
        {
            $params['CALLBACKURL'] = $to;
        }
        else
        {
            $params['MAILTO'] = $to;
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_reconcilPath), $params);
    }

    // Be2bill toolkit methods

    /**
     * @param $identifier
     * @param $password
     */
    public function setCredentials($identifier, $password)
    {
        $this->_identifier = $identifier;
        $this->_password   = $password;
    }

    /**
     * @param $urls
     */
    public function setUrls($urls)
    {
        if (is_array($urls))
        {
            $this->_urls = $urls;
        }
        else
        {
            $this->_urls = array($urls);
        }

    }

    /**
     * @param array $params
     * @return string
     */
    public function hash($params = array())
    {
        $clear_string = $this->_password;

        ksort($params);
        foreach ($params as $key => $value)
        {
            if (is_array($value))
            {
                ksort($value);
                foreach ($value as $index => $val)
                {
                    $clear_string .= $key . '[' . $index . ']=' . $val . $this->_password;
                }
            }
            else
            {
                if ($key == 'HASH')
                {
                    // Skip HASH parameter if supplied
                    continue;
                }
                else
                {
                    $clear_string .= $key . '=' . $value . $this->_password;
                }
            }
        }

        return hash('sha256', $clear_string);
    }

    /**
     * Check a hash received in a notification/redirection URL
     *
     * @param array $params The POST
     * @return bool
     */
    public function checkHash($params)
    {
        $received_hash   = $params['HASH'];
        $calculated_hash = $this->hash($params);

        return $received_hash == $calculated_hash;
    }

    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
    }

    // Internals
    /**
     * @param $by
     * @param $id
     * @param $to
     * @param $compression
     * @return bool|mixed
     */
    protected function _getTransactions($by, $id, $to, $compression)
    {
        $params["OPERATIONTYPE"] = 'getTransactions';
        $params['IDENTIFIER']    = $this->_identifier;
        $params['VERSION']       = self::API_VERSION;

        if (is_array($id))
        {
            $id = implode(';', $id);
        }

        if ($by == 'ORDERID')
        {
            $params['ORDERID'] = $id;
        }
        elseif ($by == 'TRANSACTIONID')
        {
            $params['TRANSACTIONID'] = $id;
        }

        $params["COMPRESSION"]   = $compression;

        if ($this->_isHttpUrl($to))
        {
            $params['CALLBACKURL'] = $to;
        }
        else
        {
            $params['MAILTO'] = $to;
        }

        $params['HASH'] = $this->hash($params);

        return $this->_requests($this->_getURLs($this->_exportPath), $params);
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function _getURL($path)
    {
        return current($this->_getURLs($path));
    }

    /**
     * Add $path to each url in productionUrls
     * @param $path
     * @return array
     */
    protected function _getURLs($path)
    {
        // Add path to each urls
        return array_map(create_function('$elm', 'return $elm . "' . $path . '";'), $this->_urls);
    }

    /**
     * @param $mixed
     * @return string
     */
    protected function _escape($mixed)
    {
        return htmlentities($mixed, ENT_QUOTES, $this->_encoding);
    }

    /**
     * @param $operationType
     * @param $amount
     * @param $clientIdentifier
     * @param $description
     * @param array $htmlOptions
     * @param array $options
     * @return string
     */
    protected function _buildFormButton($operationType, $clientIdentifier, $description, $htmlOptions = array(), $options = array())
    {
        $params = $options;

        $params['IDENTIFIER']    = $this->_identifier;
        $params['OPERATIONTYPE'] = $operationType;
        $params['CLIENTIDENT']   = $clientIdentifier;
        $params['DESCRIPTION']   = $description;
        $params['VERSION']       = self::API_VERSION;

        if (!isset($options['ORDERID']))
        {
            $params['ORDERID'] = uniqid();
        }

        $params['HASH'] = $this->hash($params);

        return $this->_buildFormHtml($params, $htmlOptions);
    }

    /**
     * @param $fields
     * @return string
     */
    protected function _buildHiddenInputs($fields)
    {
        $html = '';
        foreach ($fields as $key => $value)
        {
            if (is_array($value))
            {
                foreach ($value as $subKey => $subValue)
                {
                    // Build input like :<input type="hidden" name="AMOUNTS[XXXX-YY-ZZ]" value="100" />
                    $html .= $this->_buildHiddenInput($key . '[' . $subKey . ']', $subValue);
                }
            }
            else
            {
                $html .= $this->_buildHiddenInput($key, $value);
            }
        }

        return $html;
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    protected function _buildHiddenInput($key, $value)
    {
        return '<input type="hidden" name="' . $this->_escape($key) . '" value="' . $this->_escape($value) . '">';
    }

    /**
     * @param $options
     * @return string
     */
    protected function _buildSubmit($options)
    {
        $attribs = '';

        foreach ($options as $key => $value)
        {
            $attribs .= $this->_escape($key) . '="' . $this->_escape($value) . '" ';
        }

        $html = '<input type="submit" ' . $attribs . ' />';

        return $html;
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     */
    protected function _request($url, $params = array())
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array('method' => $params['OPERATIONTYPE'], 'params' => $params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::REQUEST_TIMEOUT);

        $result = curl_exec($curl);

        if ($result === false)
        {
            trigger_error(E_USER_WARNING, 'Unable to process request: ' . curl_error($curl));

            return false;
        }
        else
        {
            return json_decode($result, true);
        }
    }

    /**
     * @param $urls
     * @param array $params
     * @return bool|string
     */
    protected function _requests($urls, $params = array())
    {
        foreach ($urls as $url)
        {
            $result = $this->_request($url, $params);

            if ($result)
            {
                return $result;
            }
        }

        return false;
    }

    /**
     * @param $params
     * @param array $htmlOptions
     * @return string
     */
    protected function _buildFormHtml($params, $htmlOptions = array())
    {
        // Return something like $prodUrl / formPath
        $html = '<form method="post" action="' . $this->_getURL($this->_formPath) . '">';

        $html .= $this->_buildHiddenInputs($params);

        $html .= $this->_buildSubmit($htmlOptions);
        $html .= '</form>';

        return $html;
    }

    /**
     * @param $url
     * @return bool
     */
    protected function _isHttpUrl($url)
    {
        return preg_match('#^https?://.+#', $url) == 1;
    }
}