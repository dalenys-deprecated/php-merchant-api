<?php

/**
 * Implements Be2bill payment API
 * @version 1.0.0
 */
class Be2bill_Api_DirectLinkClient
{
    const API_VERSION = '2.0';

    // API urls
    protected $urls = array();

    // Paths
    protected $directLinkPath = '/front/service/rest/process';
    protected $exportPath = '/front/service/rest/export';
    protected $reconciliationPath = '/front/service/rest/reconciliation';

    // Credentials
    protected $identifier = null;
    protected $password = null;

    // Internals

    /**
     * @var Be2bill_Api_Sender_Sendable
     */
    protected $sender = null;

    /**
     * @var Be2bill_Api_Hash_Hashable
     */
    protected $hash = null;

    /**
     * @param                                 $identifier
     * @param                                 $password
     * @param array                           $urls
     * @param Be2bill_Api_Sender_Sendable     $sender
     * @param Be2bill_Api_Hash_Hashable       $hash
     */
    public function __construct(
        $identifier,
        $password,
        array $urls,
        Be2bill_Api_Sender_Sendable $sender,
        Be2bill_Api_Hash_Hashable $hash
    ) {
        $this->setCredentials($identifier, $password);
        $this->setUrls($urls);

        $this->sender = $sender;
        $this->hash   = $hash;
    }

    /**
     * @param $identifier
     * @param $password
     */
    public function setCredentials($identifier, $password)
    {
        $this->identifier = $identifier;
        $this->password   = $password;
    }

    public function payment(
        $cardPan,
        $cardDate,
        $cardCryptogram,
        $cardFullName,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        if (is_array($amount)) {
            $params["AMOUNTS"] = $amount;
        } else {
            $params["AMOUNT"] = $amount;
        }

        $params['OPERATIONTYPE']    = 'payment';
        $params['CARDCODE']         = $cardPan;
        $params['CARDVALIDITYDATE'] = $cardDate;
        $params['CARDCVV']          = $cardCryptogram;
        $params['CARDFULLNAME']     = $cardFullName;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $description,
            $clientIP,
            $clientUserAgent,
            $params
        );
    }

    public function authorization(
        $cardPan,
        $cardDate,
        $cardCryptogram,
        $cardFullName,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        $params['OPERATIONTYPE']    = 'authorization';
        $params['CARDCODE']         = $cardPan;
        $params['CARDVALIDITYDATE'] = $cardDate;
        $params['CARDCVV']          = $cardCryptogram;
        $params['CARDFULLNAME']     = $cardFullName;
        $params["AMOUNT"]           = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $description,
            $clientIP,
            $clientUserAgent,
            $params
        );
    }

    public function credit(
        $cardPan,
        $cardDate,
        $cardCryptogram,
        $cardFullName,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        $params['OPERATIONTYPE']    = 'credit';
        $params['CARDCODE']         = $cardPan;
        $params['CARDVALIDITYDATE'] = $cardDate;
        $params['CARDCVV']          = $cardCryptogram;
        $params['CARDFULLNAME']     = $cardFullName;
        $params["AMOUNT"]           = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $description,
            $clientIP,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to refund a transaction and will return the result formatted as an array.
     *
     * @param       $transactionId
     * @param       $orderId
     * @param       $description
     * @param array $options
     * @return bool|mixed
     */
    public function refund($transactionId, $orderId, $description, array $options = array())
    {
        $params = $options;

        $params['IDENTIFIER']    = $this->identifier;
        $params['OPERATIONTYPE'] = 'refund';
        $params['DESCRIPTION']   = $description;
        $params['TRANSACTIONID'] = $transactionId;
        $params['VERSION']       = self::API_VERSION;
        $params['ORDERID']       = $orderId;

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->directLinkPath), $params);
    }

    /**
     * This method is used to capture an authorization and will return the result formatted as an array.
     *
     * @param       $transactionId
     * @param       $orderId
     * @param       $description
     * @param array $options
     * @return bool|mixed
     */
    public function capture($transactionId, $orderId, $description, array $options = array())
    {
        $params = $options;

        $params['IDENTIFIER']    = $this->identifier;
        $params['OPERATIONTYPE'] = 'capture';
        $params['VERSION']       = self::API_VERSION;
        $params['DESCRIPTION']   = $description;
        $params['TRANSACTIONID'] = $transactionId;
        $params['ORDERID']       = $orderId;

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->directLinkPath), $params);
    }

    /**
     * This method is used to initiate a oneClick transaction using an ALIAS and will return the result
     * formatted as an array.
     *
     * @param       $alias
     * @param       $amount
     * @param       $orderId
     * @param       $clientIdentifier
     * @param       $clientEmail
     * @param       $description
     * @param       $clientIP
     * @param       $clientUserAgent
     * @param array $options
     * @return bool|mixed
     */
    public function oneClickPayment(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        $params['OPERATIONTYPE'] = 'payment';
        $params['ALIAS']         = $alias;
        $params['ALIASMODE']     = 'oneclick';
        $params["AMOUNT"]        = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $description,
            $clientIP,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to initiate a oneClick transaction using an ALIAS and will return the result
     * formatted as an array.
     *
     * @param       $alias
     * @param       $amount
     * @param       $orderId
     * @param       $clientIdentifier
     * @param       $clientEmail
     * @param       $description
     * @param       $clientIP
     * @param       $clientUserAgent
     * @param array $options
     * @return bool|string
     */
    public function oneClickAuthorization(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        $params['OPERATIONTYPE'] = 'authorization';
        $params['ALIAS']         = $alias;
        $params['ALIASMODE']     = 'oneclick';
        $params["AMOUNT"]        = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $description,
            $clientIP,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to initiate a subscription transaction using an ALIAS and will return the result
     * formatted as an array.
     *
     * @param       $alias
     * @param       $amount
     * @param       $orderId
     * @param       $clientIdentifier
     * @param       $clientEmail
     * @param       $description
     * @param       $clientIP
     * @param       $clientUserAgent
     * @param array $options
     * @return bool|string
     */
    public function subscriptionPayment(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        $params['OPERATIONTYPE'] = 'payment';
        $params['ALIASMODE']     = 'subscription';
        $params['ALIAS']         = $alias;
        $params["AMOUNT"]        = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $description,
            $clientIP,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to initiate a subscription transaction using an ALIAS and will return the result
     * formatted as an array.
     *
     * @param       $alias
     * @param       $amount
     * @param       $orderId
     * @param       $clientIdentifier
     * @param       $clientEmail
     * @param       $description
     * @param       $clientIP
     * @param       $clientUserAgent
     * @param array $options
     * @return bool|string
     */
    public function subscriptionAuthorization(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        $params['OPERATIONTYPE'] = 'authorization';
        $params['ALIASMODE']     = 'subscription';
        $params['ALIAS']         = $alias;
        $params["AMOUNT"]        = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $description,
            $clientIP,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to stop a N times transaction and will return the result formatted as an array.
     *
     * @param       $scheduleId
     * @param array $options
     * @return bool|string
     */
    public function stopNTimes($scheduleId, array $options = array())
    {
        $params = $options;

        $params['IDENTIFIER']    = $this->identifier;
        $params['OPERATIONTYPE'] = 'stopntimes';
        $params['SCHEDULEID']    = $scheduleId;
        $params['VERSION']       = self::API_VERSION;

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->directLinkPath), $params);
    }

// Redirection

    /**
     * This method is used to redirect a cardholder to an alternative payment provider (like wallets) and will
     * return the result formatted as an array.
     * In case of success, you will have to display the $result[REDIRECTHTML] code with a base64_decode to redirect
     * the user to the payment page.
     *
     * @param       $amount
     * @param       $orderId
     * @param       $clientIdentifier
     * @param       $clientEmail
     * @param       $description
     * @param       $clientIP
     * @param       $clientUserAgent
     * @param array $options
     * @return bool|string
     */
    public function redirectForPayment(
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        if (!isset($options['OPERATIONTYPE'])) {
            $params['OPERATIONTYPE'] = 'payment';
        }

        $params['AMOUNT'] = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $description,
            $clientIP,
            $clientUserAgent,
            $params
        );
    }

    // Export methods

    /**
     * This method is used to recover one or several transactions using the TRANSACTIONID.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param        $transactionId
     * @param        $destination
     * @param string $compression
     * @return bool|mixed
     */
    public function getTransactionsByTransactionId(
        $transactionId,
        $destination,
        $compression = 'GZIP'
    ) {
        return $this->getTransactions('TRANSACTIONID', $transactionId, $destination, $compression);
    }

    /**
     * This method is used to recover one or several transactions using the ORDERID.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param        $orderId
     * @param        $destination
     * @param string $compression
     * @return bool|mixed
     */
    public function getTransactionsByOrderId(
        $orderId,
        $destination,
        $compression = 'GZIP'
    ) {
        return $this->getTransactions('ORDERID', $orderId, $destination, $compression);
    }

    /**
     * This method is used to recover the list of transactions for a given day or month.
     * This method only ask for sending a report. The report will be sent by email or http request.
     * This will return the result of the report creation request
     *
     * @param        $date
     * @param        $destination
     * @param string $compression
     * @param        $options
     * @return bool|mixed
     */
    public function exportTransactions(
        $date,
        $destination,
        $compression = 'GZIP',
        array $options = array()
    ) {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["DATE"]          = $date;
        $params["OPERATIONTYPE"] = 'exportTransactions';
        $params['IDENTIFIER']    = $this->identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
        } else {
            $params['MAILTO'] = $destination;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->exportPath), $params);
    }

    /**
     * This method is used to recover the list of chargebacks for a given day or month.
     * This method only ask for sending a report. The report will be sent by email or http request.
     * This will return the result of the report creation request
     *
     * @param        $date
     * @param        $destination
     * @param        $compression
     * @param        $options
     * @return bool|mixed
     */
    public function exportChargebacks(
        $date,
        $destination,
        $compression = 'GZIP',
        array $options = array()
    ) {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["OPERATIONTYPE"] = 'exportChargebacks';
        $params["DATE"]          = $date;
        $params['IDENTIFIER']    = $this->identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
        } else {
            $params['MAILTO'] = $destination;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->exportPath), $params);
    }

    /**
     * This method is used to recover the reconciliations for a given month.
     * This method only ask for sending a report. The report will be sent by email or http request.
     * This will return the result of the report creation request
     *
     * @param        $date
     * @param        $destination
     * @param        $compression
     * @param        $options
     * @return bool|mixed
     */
    public function exportReconciliation(
        $date,
        $destination,
        $compression = 'GZIP',
        $options = array()
    ) {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["OPERATIONTYPE"] = 'export';
        $params["DATE"]          = $date;
        $params['IDENTIFIER']    = $this->identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
        } else {
            $params['MAILTO'] = $destination;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->reconciliationPath), $params);
    }

    // Be2bill toolkit methods

    /**
     * @param array $params
     * @return string
     */
    public function hash(array $params = array())
    {
        return $this->hash->compute($this->password, $params);
    }

    /**
     * Check a hash received in a notification/redirection URL
     *
     * @param array $params The POST or GET variable to verify
     * @return bool
     */
    public function checkHash($params)
    {
        return $this->hash->checkHash($this->password, $params);
    }

    /**
     * @param $urls
     */
    public function setUrls($urls)
    {
        if (is_array($urls)) {
            $this->urls = $urls;
        } else {
            $this->urls = array($urls);
        }
    }

    /**
     * Send requests with a fallback system
     * @param       $urls
     * @param array $params
     * @return bool|string
     */
    protected function requests($urls, array $params = array())
    {
        foreach ($urls as $url) {
            $result = $this->requestOne($url, $params);

            if ($result) {
                return $result;
            } elseif ($this->sender->shouldRetry() == false) {
                return false;
            }
        }

        return false;
    }

    /**
     * Send one request
     * @param       $url
     * @param array $params
     * @return mixed
     */
    protected function requestOne($url, array $params = array())
    {
        $requestParams = array('method' => $params['OPERATIONTYPE'], 'params' => $params);

        $sender = $this->sender;
        $result = $sender->send($url, $requestParams);

        return json_decode($result, true);
    }

    /**
     * Add $path to each url in productionUrls
     * @param $path
     * @return array
     */
    protected function getURLs($path)
    {
        // Add path to each urls
        return array_map(create_function('$elm', 'return $elm . "' . $path . '";'), $this->urls);
    }

    // Internals
    protected function transaction(
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    ) {
        $params = $options;

        $params['ORDERID']         = $orderId;
        $params['CLIENTIDENT']     = $clientIdentifier;
        $params['CLIENTEMAIL']     = $clientEmail;
        $params['DESCRIPTION']     = $description;
        $params['CLIENTUSERAGENT'] = $clientUserAgent;
        $params['CLIENTIP']        = $clientIP;
        $params['IDENTIFIER']      = $this->identifier;
        $params['VERSION']         = self::API_VERSION;

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->directLinkPath), $params);
    }

    /**
     * @param $searchBy
     * @param $id
     * @param $destination
     * @param $compression
     * @return bool|mixed
     */
    protected function getTransactions(
        $searchBy,
        $id,
        $destination,
        $compression
    ) {
        $params["OPERATIONTYPE"] = 'getTransactions';
        $params['IDENTIFIER']    = $this->identifier;
        $params['VERSION']       = self::API_VERSION;

        if (is_array($id)) {
            $id = implode(';', $id);
        }

        if ($searchBy == 'ORDERID') {
            $params['ORDERID'] = $id;
        } elseif ($searchBy == 'TRANSACTIONID') {
            $params['TRANSACTIONID'] = $id;
        }

        $params["COMPRESSION"] = $compression;

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
        } else {
            $params['MAILTO'] = $destination;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->exportPath), $params);
    }

    /**
     * @param $url
     * @return bool
     */
    protected function isHttpUrl($url)
    {
        return preg_match('#^https?://.+#', $url) == 1;
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function getURL($path)
    {
        return current($this->getURLs($path));
    }
}
