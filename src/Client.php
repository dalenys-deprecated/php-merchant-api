<?php

/**
 * Implements Be2bill payment API
 * @version 1.0.0
 */
class Be2bill_Api_Client
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
     * @var Be2bill_Api_Renderer_Renderable
     */
    protected $renderer = null;

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
     * @param Be2bill_Api_Renderer_Renderable $renderer
     * @param Be2bill_Api_Sender_Sendable     $sender
     * @param Be2bill_Api_Hash_Hashable       $sender
     */
    public function __construct(
        $identifier,
        $password,
        array $urls,
        Be2bill_Api_Renderer_Renderable $renderer,
        Be2bill_Api_Sender_Sendable $sender,
        Be2bill_Api_Hash_Hashable $hash
    )
    {
        $this->setCredentials($identifier, $password);
        $this->setUrls($urls);

        $this->renderer = $renderer;
        $this->sender   = $sender;
        $this->hash     = $hash;
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

    // Autoloading

    /**
     * Register a standard autoloader for the Be2bill Client API
     */
    public static function registerAutoloader()
    {
        spl_autoload_register(__CLASS__ . '::autoloader');
    }

    /**
     * @param $className string The class name
     */
    public static function autoloader($className)
    {
        $prefix = 'Be2bill_Api';

        $len = strlen($prefix);
        if (strncmp($prefix, $className, $len) !== 0) {
            // skip this autoloader
            return;
        }
        $relative_class = substr($className, $len + 1);
        $file           = str_replace('_', DIRECTORY_SEPARATOR, $relative_class) . '.php';

        require $file;
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
     * @param string        $orderId
     * @param string        $clientIdentifier
     * @param string        $description
     * @param array         $htmlOptions An array of HTML attributes to add to the submit or form button (allowing to change name, style, class attribute etc.).
     * Example:
     * <code>
     * $htmlOptions['SUBMIT'] = array('class' => 'my_class');
     * $htmlOptions['FORM'] = array('class' => 'my_form', 'target' => 'my_target');
     * </code>
     * @param array         $options Others be2bill options. See Be2bill documentation for more information (3DS, CREATEALIAS, etc.)
     * @return string The HTML output to display
     */
    public function buildPaymentFormButton(
        $amount,
        $orderId,
        $clientIdentifier,
        $description,
        array $htmlOptions = array(),
        array $options = array()
    )
    {
        $params = $options;

        // Handle N-Time payments
        if (is_array($amount)) {
            $params["AMOUNTS"] = $amount;
        } else {
            $params["AMOUNT"] = $amount;
        }

        return $this->buildProcessButton('payment', $orderId, $clientIdentifier, $description, $htmlOptions, $params);
    }

    /**
     * Build form authorization and submit button
     *
     * This method will return the form authorization and all hidden input configuring the be2bill transaction.
     * You will have to call the {@link capture} method to confirm this authorization
     * @param int          $amount
     * @param int          $orderId
     * @param string       $clientIdentifier
     * @param string       $description
     * @param array         $htmlOptions An array of HTML attributes to add to the submit or form button (allowing to change name, style, class attribute etc.).
     * Example:
     * <code>
     * $htmlOptions['SUBMIT'] = array('class' => 'my_class');
     * $htmlOptions['FORM'] = array('class' => 'my_form', 'target' => 'my_target');
     * </code>
     * @param        array $options Others be2bill options. See Be2bill documentation for more information (3DS, CREATEALIAS etc.)
     * @see capture
     * @return string The HTML output to display
     */
    public function buildAuthorizationFormButton(
        $amount,
        $orderId,
        $clientIdentifier,
        $description,
        array $htmlOptions = array(),
        array $options = array()
    )
    {
        $params = $options;

        $params["AMOUNT"] = $amount;

        return $this->buildProcessButton('authorization', $orderId, $clientIdentifier, $description, $htmlOptions, $params);
    }

    // Editing API

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
     * This method is used to initiate a oneClick transaction using an ALIAS and will return the result formatted as an array.
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
    )
    {
        $params = $options;

        if (is_array($amount)) {
            $params["AMOUNTS"] = $amount;
        } else {
            $params["AMOUNT"] = $amount;
        }

        $params['OPERATIONTYPE'] = 'payment';
        $params['ALIASMODE']     = 'oneclick';

        return $this->rebillTransaction($alias, $amount, $orderId, $clientIdentifier, $clientEmail, $description, $clientIP, $clientUserAgent, $params);
    }

    /**
     * This method is used to initiate a oneClick transaction using an ALIAS and will return the result formatted as an array.
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
    )
    {
        $params = $options;

        $params["AMOUNT"]        = $amount;
        $params['OPERATIONTYPE'] = 'authorization';
        $params['ALIASMODE']     = 'oneclick';

        return $this->rebillTransaction($alias, $amount, $orderId, $clientIdentifier, $clientEmail, $description, $clientIP, $clientUserAgent, $params);
    }

    /**
     * This method is used to initiate a subscription transaction using an ALIAS and will return the result formatted as an array.
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
    )
    {
        $params = $options;

        if (is_array($amount)) {
            $params["AMOUNTS"] = $amount;
        } else {
            $params["AMOUNT"] = $amount;
        }

        $params['OPERATIONTYPE'] = 'payment';
        $params['ALIASMODE']     = 'subscription';

        return $this->rebillTransaction($alias, $amount, $orderId, $clientIdentifier, $clientEmail, $description, $clientIP, $clientUserAgent, $params);
    }

    /**
     * This method is used to initiate a subscription transaction using an ALIAS and will return the result formatted as an array.
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
    )
    {
        $params = $options;

        $params["AMOUNT"]        = $amount;
        $params['OPERATIONTYPE'] = 'authorization';
        $params['ALIASMODE']     = 'subscription';

        return $this->rebillTransaction($alias, $amount, $orderId, $clientIdentifier, $clientEmail, $description, $clientIP, $clientUserAgent, $params);
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
     * This method is used to redirect a cardholder to an alternative payment provider (like wallets) and will return the result formatted as an array.
     * In case of success, you will have to display the $result[REDIRECTHTML] code with a base64_decode to redirect the user to the payment page.
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
    )
    {
        $params = $options;

        $params["AMOUNT"]          = $amount;
        $params['ORDERID']         = $orderId;
        $params['CLIENTIDENT']     = $clientIdentifier;
        $params['CLIENTEMAIL']     = $clientEmail;
        $params['DESCRIPTION']     = $description;
        $params['CLIENTUSERAGENT'] = $clientUserAgent;
        $params['CLIENTIP']        = $clientIP;
        $params['IDENTIFIER']      = $this->identifier;
        $params['VERSION']         = self::API_VERSION;

        if (!isset($options['OPERATIONTYPE'])) {
            $params['OPERATIONTYPE'] = 'payment';
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->directLinkPath), $params);
    }

    // Export methods

    /**
     * This method is used to recover one or several transactions using the TRANSACTIONID.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param        $transactionId
     * @param        $to
     * @param string $compression
     * @return bool|mixed
     */
    public function getTransactionsByTransactionId($transactionId, $to, $compression = 'GZIP')
    {
        return $this->getTransactions('TRANSACTIONID', $transactionId, $to, $compression);
    }

    /**
     * This method is used to recover one or several transactions using the ORDERID.
     * This will return the result formatted as a compressed CSV file.
     *
     * @param        $orderId
     * @param        $to
     * @param string $compression
     * @return bool|mixed
     */
    public function getTransactionsByOrderId($orderId, $to, $compression = 'GZIP')
    {
        return $this->getTransactions('ORDERID', $orderId, $to, $compression);
    }

    /**
     * This method is used to recover the list of transactions for a given day or month.
     * This method only ask for sending a report. The report will be sent by email or http request.
     * This will return the result of the report creation request
     *
     * @param        $date
     * @param        $to
     * @param string $compression
     * @param        $options
     * @return bool|mixed
     */
    public function exportTransactions($date, $to, $compression = 'GZIP', array $options = array())
    {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["DATE"]          = $date;
        $params["OPERATIONTYPE"] = 'exportTransactions';
        $params['IDENTIFIER']    = $this->identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->isHttpUrl($to)) {
            $params['CALLBACKURL'] = $to;
        } else {
            $params['MAILTO'] = $to;
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
     * @param        $to
     * @param        $compression
     * @param        $options
     * @return bool|mixed
     */
    public function exportChargebacks($date, $to, $compression = 'GZIP', array $options = array())
    {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["OPERATIONTYPE"] = 'exportChargebacks';
        $params["DATE"]          = $date;
        $params['IDENTIFIER']    = $this->identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->isHttpUrl($to)) {
            $params['CALLBACKURL'] = $to;
        } else {
            $params['MAILTO'] = $to;
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
     * @param        $to
     * @param        $compression
     * @param        $options
     * @return bool|mixed
     */
    public function exportReconciliation($date, $to, $compression = 'GZIP', $options = array())
    {
        $params = $options;

        $params["COMPRESSION"]   = $compression;
        $params["OPERATIONTYPE"] = 'export';
        $params["DATE"]          = $date;
        $params['IDENTIFIER']    = $this->identifier;
        $params['VERSION']       = self::API_VERSION;

        if ($this->isHttpUrl($to)) {
            $params['CALLBACKURL'] = $to;
        } else {
            $params['MAILTO'] = $to;
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
        $received_hash   = $params['HASH'];
        $calculated_hash = $this->hash($params);

        return $received_hash == $calculated_hash;
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

    /**
     * @param       $operationType
     * @param       $orderId
     * @param       $clientIdentifier
     * @param       $description
     * @param array $htmlOptions
     * @param array $options
     * @return string
     */
    protected function buildProcessButton(
        $operationType,
        $orderId,
        $clientIdentifier,
        $description,
        array $htmlOptions = array(),
        array $options = array()
    )
    {
        $params = $options;

        $params['IDENTIFIER']    = $this->identifier;
        $params['OPERATIONTYPE'] = $operationType;
        $params['ORDERID']       = $orderId;
        $params['CLIENTIDENT']   = $clientIdentifier;
        $params['DESCRIPTION']   = $description;
        $params['VERSION']       = self::API_VERSION;

        $params['HASH'] = $this->hash($params);

        $renderer = $this->renderer;

        return $renderer->render($params, $htmlOptions);
    }

    /**
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
    protected function rebillTransaction(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $description,
        $clientIP,
        $clientUserAgent,
        array $options = array()
    )
    {
        $params = $options;

        $params['ORDERID']         = $orderId;
        $params['CLIENTIDENT']     = $clientIdentifier;
        $params['CLIENTEMAIL']     = $clientEmail;
        $params['DESCRIPTION']     = $description;
        $params['CLIENTUSERAGENT'] = $clientUserAgent;
        $params['CLIENTIP']        = $clientIP;
        $params['ALIAS']           = $alias;
        $params['IDENTIFIER']      = $this->identifier;
        $params['VERSION']         = self::API_VERSION;

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->directLinkPath), $params);
    }

    /**
     * @param $by
     * @param $id
     * @param $to
     * @param $compression
     * @return bool|mixed
     */
    protected function getTransactions($by, $id, $to, $compression)
    {
        $params["OPERATIONTYPE"] = 'getTransactions';
        $params['IDENTIFIER']    = $this->identifier;
        $params['VERSION']       = self::API_VERSION;

        if (is_array($id)) {
            $id = implode(';', $id);
        }

        if ($by == 'ORDERID') {
            $params['ORDERID'] = $id;
        } elseif ($by == 'TRANSACTIONID') {
            $params['TRANSACTIONID'] = $id;
        }

        $params["COMPRESSION"] = $compression;

        if ($this->isHttpUrl($to)) {
            $params['CALLBACKURL'] = $to;
        } else {
            $params['MAILTO'] = $to;
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
