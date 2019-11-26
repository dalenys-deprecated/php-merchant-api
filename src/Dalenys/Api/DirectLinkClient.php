<?php

/**
 * Directlink client
 *
 * @package Dalenys
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Implements Dalenys payment API
 */
class Dalenys_Api_DirectLinkClient
{
    /**
     * @var string API VERSION
     */
    protected $version = '2.0';

    /**
     * @var array The API urls (with fallback)
     */
    protected $urls = [];

    // Paths

    /**
     * @var string The directlink part
     */
    protected $directLinkPath = '/front/service/rest/process';

    /**
     * @var string The export part
     */
    protected $exportPath = '/front/service/rest/export';

    /**
     * @var string The reconciliation part
     */
    protected $reconciliationPath = '/front/service/rest/reconciliation';

    // Credentials

    /**
     * @var string The Dalenys identifier
     */
    protected $identifier;

    /**
     * @var string The Dalenys password
     */
    protected $password;

    // Internals

    /**
     * @var Dalenys_Api_Sender_Sendable The sender object
     */
    protected $sender = null;

    /**
     * @var Dalenys_Api_Hash_Hashable The hashing object
     */
    protected $hash = null;

    /**
     * Instanciate
     *
     * @param string                      $identifier Dalenys identifier
     * @param string                      $password Dalenys password
     * @param array                       $urls Dalenys URLS
     * @param Dalenys_Api_Sender_Sendable $sender The sender object to use
     * @param Dalenys_Api_Hash_Hashable   $hash The hashing object to use
     */
    public function __construct(
        $identifier,
        $password,
        array $urls,
        Dalenys_Api_Sender_Sendable $sender,
        Dalenys_Api_Hash_Hashable $hash
    )
    {
        $this->setCredentials($identifier, $password);
        $this->setUrls($urls);

        $this->sender = $sender;
        $this->hash = $hash;
    }

    /**
     * Configurate API credentials
     *
     * @param string $identifier The Dalenys identifier
     * @param string $password The Dalenys password
     */
    public function setCredentials($identifier, $password)
    {
        $this->identifier = $identifier;
        $this->password = $password;
    }

    /**
     * Set default Dalenys VERSION parameter
     *
     * @param string $version The VERSION number (ex: 3.0)
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Directlink payment
     *
     * You have to specify SENSIBLE payment data (card, cryptogramm...)
     *
     * Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR payment
     * $result = $api->payment(
     *  '1111222233334444',
     *  '04-15',
     *  132,
     *  'john doe',
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     * );
     *
     * print_r($result);
     * ```
     *
     * @api
     * @see http://fr.pcisecuritystandards.org/minisite/en/
     * @param string $cardPan The card number (sensible)
     * @param string $cardDate The card validity date (sensible) Format mm-yy
     * @param string $cardCryptogram The card cryptogram (sensible)
     * @param string $cardFullName The card full name
     * @param int    $amount The amount (in the currency smallest subdivision Ex: $amount = 100 > 1€)
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier (ex: login)
     * @param string $clientEmail The client email
     * @param string $clientIP The client public IP
     * @param string $description The transaction descrtiption
     * @param string $clientUserAgent The client user agent
     * @param array  $options Some other payment options (@see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'payment',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function payment(
        $cardPan,
        $cardDate,
        $cardCryptogram,
        $cardFullName,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;
        $params = $this->amountOrAmounts($amount, $params);

        $params['OPERATIONTYPE'] = 'payment';
        $params['CARDCODE'] = $cardPan;
        $params['CARDVALIDITYDATE'] = $cardDate;
        $params['CARDCVV'] = $cardCryptogram;
        $params['CARDFULLNAME'] = $cardFullName;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $clientIP,
            $description,
            $clientUserAgent,
            $params
        );
    }

    /**
     * Directlink authorization
     *
     * You have to specify SENSIBLE payment data (card, cryptogramm...)
     *
     * Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR payment
     * $result = $api->authorization(
     *  '1111222233334444',
     *  '04-15',
     *  132,
     *  'john doe',
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     * );
     *
     * print_r($result);
     * ```
     *
     * @api
     * @see http://fr.pcisecuritystandards.org/minisite/en/
     * @param string $cardPan The card number (sensible)
     * @param string $cardDate The card validity date (sensible) Format mm-yy
     * @param string $cardCryptogram The card cryptogram (sensible)
     * @param string $cardFullName The card full name
     * @param int    $amount The amount (in the currency smallest subdivision Ex: $amount = 100 > 1€)
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier (ex: login)
     * @param string $clientEmail The client email
     * @param string $clientIP The client public IP
     * @param string $description The transaction descrtiption
     * @param string $clientUserAgent The client user agent
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'authorization',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function authorization(
        $cardPan,
        $cardDate,
        $cardCryptogram,
        $cardFullName,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;

        $params['OPERATIONTYPE'] = 'authorization';
        $params['CARDCODE'] = $cardPan;
        $params['CARDVALIDITYDATE'] = $cardDate;
        $params['CARDCVV'] = $cardCryptogram;
        $params['CARDFULLNAME'] = $cardFullName;
        $params["AMOUNT"] = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $clientIP,
            $description,
            $clientUserAgent,
            $params
        );
    }

    /**
     * Directlink credit
     *
     * You have to specify SENSIBLE card data (card, cryptogramm...)
     *
     * Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR credit
     * $result = $api->credit(
     *  '1111222233334444',
     *  '04-15',
     *  132,
     *  'john doe',
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     * );
     *
     * print_r($result);
     * ```
     *
     * @api
     * @see http://fr.pcisecuritystandards.org/minisite/en/
     * @param string $cardPan The card number (sensible)
     * @param string $cardDate The card validity date (sensible) Format mm-yy
     * @param string $cardCryptogram The card cryptogram (sensible)
     * @param string $cardFullName The card full name
     * @param int    $amount The amount (in the currency smallest subdivision Ex: $amount = 100 > 1€)
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier (ex: login)
     * @param string $clientEmail The client email
     * @param string $clientIP The client public IP
     * @param string $description The transaction descrtiption
     * @param string $clientUserAgent The client user agent
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'credit',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function credit(
        $cardPan,
        $cardDate,
        $cardCryptogram,
        $cardFullName,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;

        $params['OPERATIONTYPE'] = 'credit';
        $params['CARDCODE'] = $cardPan;
        $params['CARDVALIDITYDATE'] = $cardDate;
        $params['CARDCVV'] = $cardCryptogram;
        $params['CARDFULLNAME'] = $cardFullName;
        $params["AMOUNT"] = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $clientIP,
            $description,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to initiate a oneClick transaction using an ALIAS
     *
     * You have to process an authorization or a payment with the option CREATEALIAS = yes to get an ALIAS
     *
     * Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR standard payment (with alias creation)
     * $result = $api->payment(
     *  '1111222233334444',
     *  '04-15',
     *  132,
     *  'john doe',
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     *  ['CREATEALIAS' => 'yes']
     * );
     *
     * $result2 = $api->oneClickPayment(
     *  $result['ALIAS'],
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     * );
     *
     * ```
     *
     * @api
     * @param string $alias The card ALIAS
     * @param int    $amount The amount (in the currency smallest subdivision Ex: $amount = 100 > 1€)
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier
     * @param string $clientEmail The client EMAIL
     * @param string $clientIP The client public IP
     * @param string $description The transaction description
     * @param string $clientUserAgent The client user agent
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'payment',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function oneClickPayment(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;
        $params = $this->amountOrAmounts($amount, $params);

        $params['OPERATIONTYPE'] = 'payment';
        $params['ALIAS'] = $alias;
        $params['ALIASMODE'] = 'oneclick';

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $clientIP,
            $description,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to initiate a oneClick transaction using an ALIAS
     *
     * You have to process an authorization or a payment with the option CREATEALIAS = yes to get an ALIAS
     *
     * Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR standard payment (with alias creation)
     * $result = $api->payment(
     *  '1111222233334444',
     *  '04-15',
     *  132,
     *  'john doe',
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     *  ['CREATEALIAS' => 'yes']
     * );
     *
     * $result2 = $api->oneClickAuthorization(
     *  $result['ALIAS'],
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     * );
     *
     * ```
     *
     * @api
     * @param string $alias The card ALIAS
     * @param int    $amount The amount (in the currency smallest subdivision Ex: $amount = 100 > 1€)
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier
     * @param string $clientEmail The client EMAIL
     * @param string $clientIP The client public IP
     * @param string $description The transaction description
     * @param string $clientUserAgent The client user agent
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'authorization',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function oneClickAuthorization(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;

        $params['OPERATIONTYPE'] = 'authorization';
        $params['ALIAS'] = $alias;
        $params['ALIASMODE'] = 'oneclick';
        $params["AMOUNT"] = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $clientIP,
            $description,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to refund a transaction
     *
     * Usage example:
     * * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * $result = $api->refund(
     *  'A123',
     *  'order_123',
     *  'sample refund'
     * );
     *
     * ```
     *
     * @api
     * @param string $transactionId The transaction id to refund
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $description The transaction description
     * @param array  $options
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'refund',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function refund($transactionId, $orderId, $description, array $options = [])
    {
        $params = $options;

        $params['IDENTIFIER'] = $this->identifier;
        $params['OPERATIONTYPE'] = 'refund';
        $params['DESCRIPTION'] = $description;
        $params['TRANSACTIONID'] = $transactionId;
        $params['VERSION'] = $this->getVersion($options);
        $params['ORDERID'] = $orderId;

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getDirectLinkUrls(), $params);
    }

    /**
     * This method is used to capture an authorization
     *
     * Usage example:
     * * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * $result = $api->capture(
     *  'A123',
     *  'order_123',
     *  'sample refund'
     * );
     *
     * ```
     *
     * @api
     * @param string $transactionId The authorization id to capture
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $description The transaction description
     * @param array  $options
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'capture',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function capture($transactionId, $orderId, $description, array $options = [])
    {
        $params = $options;

        $params['IDENTIFIER'] = $this->identifier;
        $params['OPERATIONTYPE'] = 'capture';
        $params['VERSION'] = $this->getVersion($options);
        $params['DESCRIPTION'] = $description;
        $params['TRANSACTIONID'] = $transactionId;
        $params['ORDERID'] = $orderId;

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getDirectLinkUrls(), $params);
    }

    /**
     * This method is used to initiate a subscription transaction using an ALIAS
     *
     * You have to process an authorization or a payment with the option CREATEALIAS = yes to get an ALIAS
     *
     * Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR standard payment (with alias creation)
     * $result = $api->payment(
     *  '1111222233334444',
     *  '04-15',
     *  132,
     *  'john doe',
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     *  ['CREATEALIAS' => 'yes']
     * );
     *
     * $result2 = $api->subscriptionAuthorization(
     *  $result['ALIAS'],
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     * );
     *
     * ```
     *
     * @api
     * @param string $alias The card ALIAS
     * @param int    $amount The amount (in the currency smallest subdivision Ex: $amount = 100 > 1€)
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier
     * @param string $clientEmail The client EMAIL
     * @param string $clientIP The client public IP
     * @param string $description The transaction description
     * @param string $clientUserAgent The client user agent
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'payment',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function subscriptionAuthorization(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;

        $params['OPERATIONTYPE'] = 'authorization';
        $params['ALIASMODE'] = 'subscription';
        $params['ALIAS'] = $alias;
        $params["AMOUNT"] = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $clientIP,
            $description,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to initiate a oneClick transaction using an ALIAS
     *
     * You have to process an authorization or a payment with the option CREATEALIAS = yes to get an ALIAS
     *
     * Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR standard payment (with alias creation)
     * $result = $api->payment(
     *  '1111222233334444',
     *  '04-15',
     *  132,
     *  'john doe',
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     *  ['CREATEALIAS' => 'yes']
     * );
     *
     * $result2 = $api->subscriptionPayment(
     *  $result['ALIAS'],
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     * );
     *
     * ```
     *
     * @api
     * @param string $alias The card ALIAS
     * @param int    $amount The amount (in the currency smallest subdivision Ex: $amount = 100 > 1€)
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier
     * @param string $clientEmail The client EMAIL
     * @param string $clientIP The client public IP
     * @param string $description The transaction description
     * @param string $clientUserAgent The client user agent
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array The result array. Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Transaction succeded',
     *  'OPERATIONTYPE' => 'payment',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     * ]
     * ```
     */
    public function subscriptionPayment(
        $alias,
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;
        $params = $this->amountOrAmounts($amount, $params);

        $params['OPERATIONTYPE'] = 'payment';
        $params['ALIASMODE'] = 'subscription';
        $params['ALIAS'] = $alias;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $clientIP,
            $description,
            $clientUserAgent,
            $params
        );
    }

    /**
     * This method is used to stop a N times scheduling
     *
     *  Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR standard payment (with alias creation)
     * $result = $api->stopNTimes('A123');
     * ```
     *
     * @api
     * @param string $scheduleId The schedule id
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array
     */
    public function stopNTimes($scheduleId, array $options = [])
    {
        $params = $options;

        $params['IDENTIFIER'] = $this->identifier;
        $params['OPERATIONTYPE'] = 'stopntimes';
        $params['SCHEDULEID'] = $scheduleId;
        $params['VERSION'] = $this->getVersion($options);

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getDirectLinkUrls(), $params);
    }

    // Redirection

    /**
     * Redirect a cardholder to a hosted payment page (wallet or direct debit payment methods)
     *
     * In case of success, you will have to display the REDIRECTHTML code with a base64_decode to redirect
     * the user to the payment page.
     *
     * Usage example:
     *
     * ```php
     * $api = Dalenys_Api_ClientBuilder::buildSandboxDirectLinkClient('IDENTIFIER', 'PASSWORD');
     *
     * // 10 EUR standard payment
     * $result = $api->redirectForPayment(
     *  1000,
     *  'order_123',
     *  'john_doe42',
     *  'john.doe@mail.com',
     *  '178.152.42.44',
     *  'sample transaction',
     *  'firefox'
     *  ['CREATEALIAS' => 'yes']
     * );
     *
     * echo base64_decode($result['REDIRECTHTML']);
     * ```
     *
     * @api
     * @param int    $amount The amount (in the currency smallest subdivision Ex: $amount = 100 > 1€)
     * @param string $orderId The orderid (should be unique by transaction, but no unicity check are performed)
     * @param string $clientIdentifier The client identifier
     * @param string $clientEmail The client EMAIL
     * @param string $clientIP The client public IP
     * @param string $description The transaction description
     * @param string $clientUserAgent The client user agent
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array The result array.
     * It will contains a REDIRECTHTML wich contains a base64 redirection code for the payment supplier page.
     * Will look like:
     * ```php
     * [
     *  'CODE' => '0002',
     *  'MESSAGE' => 'Waiting for redirection',
     *  'OPERATIONTYPE' => 'payment',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     *  'REDIRECTHTML' => 'ksdjfkldsjfkldjsklfjdsfklds'
     * ]
     * ```
     */
    public function redirectForPayment(
        $amount,
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;

        if (!isset($options['OPERATIONTYPE'])) {
            $params['OPERATIONTYPE'] = 'payment';
        }

        $params['AMOUNT'] = $amount;

        return $this->transaction(
            $orderId,
            $clientIdentifier,
            $clientEmail,
            $clientIP,
            $description,
            $clientUserAgent,
            $params
        );
    }

    // Export methods

    /**
     * Get a transaction by this ID
     *
     * @api
     * @param string $transactionId The transaction ID
     * @param string $destination This parameter accept 3 possibilities:
     * - url > will throw the report to the specified URL (csv)
     * - email > will throw the report to the specified email (csv)
     * - null > will return the transaction data directly
     * @param string $compression ZIP / GZIP or BZIP
     * @return array The result array.
     * It will contains a DATA parameters wich contains the transaction
     * Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Operation succeeded',
     *  'OPERATIONTYPE' => 'payment',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     *  'DATA' => [
     *      0 => [
     *          'IDENTIFIER' => 'IDENTIFIER',
     *          // ...
     *      ]
     * ]
     * ```
     */
    public function getTransactionsByTransactionId(
        $transactionId,
        $destination = null,
        $compression = 'GZIP'
    )
    {
        return $this->getTransactions('TRANSACTIONID', $transactionId, $destination, $compression);
    }

    /**
     * Get a transaction by order ID
     *
     * @api
     * @param string $orderId The transaction orderid ID
     * @param string $destination This parameter accept 3 possibilities:
     * - url > will throw the report to the specified URL (csv)
     * - email > will throw the report to the specified email (csv)
     * - null > will return the transaction data directly
     * @param string $compression ZIP / GZIP or BZIP
     * @return array The result array.
     * It will contains a DATA parameters wich contains the transaction
     * Will look like:
     * ```php
     * [
     *  'CODE' => '0000',
     *  'MESSAGE' => 'Operation succeeded',
     *  'OPERATIONTYPE' => 'payment',
     *  'ORDERID' => 'order_13213',
     *  'TRANSACTIONID' => 'A123',
     *  'DESCRIPTOR' => 'shop'
     *  'DATA' => [
     *      0 => [
     *          'IDENTIFIER' => 'IDENTIFIER',
     *          // ...
     *      ]
     * ]
     * ```
     */
    public function getTransactionsByOrderId(
        $orderId,
        $destination = null,
        $compression = 'GZIP'
    )
    {
        return $this->getTransactions('ORDERID', $orderId, $destination, $compression);
    }

    /**
     * This method is used to recover the list of transactions for a given day or month.
     *
     * This method only ask for sending a report. The report will be sent by email or http request.
     * This will return the result of the report creation request
     *
     * @api
     * @param date   $date YYYY-MM or YYYY-MM-DD or array(startDate, endDate)
     * @param string $destination This parameter accept 2 possibilities:
     * - url > will throw the report to the specified URL (csv)
     * - email > will throw the report to the specified email (csv)
     * @param string $compression ZIP / GZIP or BZIP
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array
     */
    public function exportTransactions(
        $date,
        $destination,
        $compression = 'GZIP',
        array $options = []
    )
    {
        $params = $options;

        $params["COMPRESSION"] = $compression;
        $params["OPERATIONTYPE"] = 'exportTransactions';
        $params['IDENTIFIER'] = $this->identifier;
        $params['VERSION'] = $this->getVersion($options);

        $params = array_merge($params, $this->getDateOrDateRangeParameter($date));

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
        } else {
            $params['MAILTO'] = $destination;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->exportPath), $params);
    }

    /**
     * Export the list of chargebacked transactions for a given day or month.
     *
     * This method only ask for sending a report. The report will be sent by email or http request.
     * This will return the result of the report creation request
     *
     * @api
     * @param date   $date YYYY-MM or YYYY-MM-DD or array(startDate, endDate)
     * @param string $destination This parameter accept 2 possibilities:
     * - url > will throw the report to the specified URL (csv)
     * - email > will throw the report to the specified email (csv)
     * @param string $compression ZIP / GZIP or BZIP
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array
     */
    public function exportChargebacks(
        $date,
        $destination,
        $compression = 'GZIP',
        array $options = []
    )
    {
        $params = $options;

        $params["COMPRESSION"] = $compression;
        $params["OPERATIONTYPE"] = 'exportChargebacks';
        $params['IDENTIFIER'] = $this->identifier;
        $params['VERSION'] = $this->getVersion($options);

        $params = array_merge($params, $this->getDateOrDateRangeParameter($date));

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
        } else {
            $params['MAILTO'] = $destination;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->exportPath), $params);
    }

    /**
     * Export the reconciliation
     *
     * This method only ask for sending a report. The report will be sent by email or http request.
     * This will return the result of the report creation request
     *
     * @api
     * @param date   $date YYYY-MM or YYYY-MM-DD or array(startDate, endDate)
     * @param string $destination This parameter accept 2 possibilities:
     * - url > will throw the report to the specified URL (csv)
     * - email > will throw the report to the specified email (csv)
     * @param string $compression ZIP / GZIP or BZIP
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array
     */
    public function exportReconciliation(
        $date,
        $destination,
        $compression = 'GZIP',
        $options = []
    )
    {
        $params = $options;

        $params["COMPRESSION"] = $compression;
        $params["OPERATIONTYPE"] = 'exportReconciliation';
        $params['IDENTIFIER'] = $this->identifier;
        $params['VERSION'] = $this->getVersion($options);
        // Actually DATE interval are not available for this export
        $params['DATE'] = $date;

        $params = array_merge($params, $this->getDateOrDateRangeParameter($date));

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
        } else {
            $params['MAILTO'] = $destination;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->reconciliationPath), $params);
    }

    /**
     * Export the list of reconciled transactions for a given day or month.
     *
     * This method only ask for sending a report. The report will be sent by email or http request.
     * This will return the result of the report creation request
     *
     * @api
     * @param date   $date YYYY-MM or YYYY-MM-DD or array(startDate, endDate)
     * @param string $destination This parameter accept 2 possibilities:
     * - url > will throw the report to the specified URL (csv)
     * - email > will throw the report to the specified email (csv)
     * @param string $compression ZIP / GZIP or BZIP
     * @param array  $options Some other payment options (see http://developer.dalenys.com
     * the dalenys api reference for the full list)
     * @return array
     */
    public function exportReconciledTransactions(
        $date,
        $destination,
        $compression = 'GZIP',
        $options = []
    )
    {
        $params = $options;

        $params["COMPRESSION"] = $compression;
        $params["OPERATIONTYPE"] = 'exportReconciledTransactions';
        $params['IDENTIFIER'] = $this->identifier;
        $params['VERSION'] = $this->getVersion($options);
        // Actually DATE interval are not available for this export
        $params['DATE'] = $date;

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
        } else {
            $params['MAILTO'] = $destination;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->reconciliationPath), $params);
    }

    // Dalenys toolkit methods

    /**
     * Hash parameters
     *
     * @param array $params
     * @return string
     */
    public function hash(array $params = [])
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
     * Set dalenys base urls
     *
     * @param string|array $urls
     */
    public function setUrls($urls)
    {
        if (is_array($urls)) {
            $this->urls = $urls;
        } else {
            $this->urls = [$urls];
        }
    }

    /**
     * Send requests with a fallback system
     *
     * @param array $urls The url list to request
     * @param array $params
     * @return bool|string
     */
    public function requests($urls, array $params = [])
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
     * Return directlink url with concatened path
     *
     * @return array
     */
    public function getDirectLinkUrls()
    {
        return $this->getUrls($this->directLinkPath);
    }

    /**
     * Return identifier
     *
     * @return string $identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Return password
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Send one request
     *
     * @param       $url
     * @param array $params
     * @return mixed
     */
    protected function requestOne($url, array $params = [])
    {
        $requestParams = ['method' => $params['OPERATIONTYPE'], 'params' => $params];

        $sender = $this->sender;
        $result = $sender->send($url, $requestParams);

        return json_decode($result, true);
    }

    /**
     * Add $path to each url in productionUrls
     *
     * @param $path
     * @return array
     */
    protected function getURLs($path)
    {
        // Add path to each urls
        return array_map(function ($elm) use ($path) {
            return $elm . $path;
        }, $this->urls);
    }

    // Internals

    /**
     * Trigger a transaction
     *
     * @param       $orderId
     * @param       $clientIdentifier
     * @param       $clientEmail
     * @param       $clientIP
     * @param       $description
     * @param       $clientUserAgent
     * @param array $options
     * @return bool|string
     */
    protected function transaction(
        $orderId,
        $clientIdentifier,
        $clientEmail,
        $clientIP,
        $description,
        $clientUserAgent,
        array $options = []
    )
    {
        $params = $options;

        $params['ORDERID'] = $orderId;
        $params['CLIENTIDENT'] = $clientIdentifier;
        $params['CLIENTEMAIL'] = $clientEmail;
        $params['DESCRIPTION'] = $description;
        $params['CLIENTUSERAGENT'] = $clientUserAgent;
        $params['CLIENTIP'] = $clientIP;
        $params['IDENTIFIER'] = $this->identifier;
        $params['VERSION'] = $this->getVersion($options);

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getDirectLinkUrls(), $params);
    }

    /**
     * Export a transaction
     *
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
    )
    {
        $params["OPERATIONTYPE"] = 'getTransactions';
        $params['IDENTIFIER'] = $this->identifier;
        $params['VERSION'] = $this->version;

        if (is_array($id)) {
            $id = implode(';', $id);
        }

        if ($searchBy == 'ORDERID') {
            $params['ORDERID'] = $id;
        } elseif ($searchBy == 'TRANSACTIONID') {
            $params['TRANSACTIONID'] = $id;
        }

        if ($this->isHttpUrl($destination)) {
            $params['CALLBACKURL'] = $destination;
            $params["COMPRESSION"] = $compression;
        } elseif ($this->isMail($destination)) {
            $params['MAILTO'] = $destination;
            $params["COMPRESSION"] = $compression;
        }

        $params['HASH'] = $this->hash($params);

        return $this->requests($this->getURLs($this->exportPath), $params);
    }

    /**
     * Return true if $url is a http/https url
     *
     * @param $url
     * @return bool
     */
    protected function isHttpUrl($url)
    {
        return preg_match('#^https?://.+#', $url) == 1;
    }

    /**
     * Return true if $mail is a valid email
     *
     * @param $mail
     * @return bool
     */
    protected function isMail($mail)
    {
        return preg_match('/.+@.+\..{2,}/', $mail) == 1;
    }

    /**
     * Return the first url with path
     *
     * @param $path
     * @return string
     */
    protected function getURL($path)
    {
        return current($this->getURLs($path));
    }

    /**
     * Handle DATE or STARTDATE/ENDDATE parameters for export methods
     *
     * @param string|array $date
     * @return mixed
     */
    protected function getDateOrDateRangeParameter($date)
    {
        $result = [];

        if (is_array($date) && sizeof($date) == 2) {
            $result['STARTDATE'] = $date[0];
            $result['ENDDATE'] = $date[1];
        } else {
            $result["DATE"] = $date;
        }

        return $result;
    }

    /**
     * Get Dalenys API VERSION
     *
     * @param array $options
     * @return string The version number
     */
    protected function getVersion(array $options = [])
    {
        if (isset($options['VERSION'])) {
            return $options['VERSION'];
        } else {
            return $this->version;
        }
    }

    /**
     * Handle amount or ntimes amounts parameter
     *
     * @param integer $amount
     * @param array   $params
     * @return array Edited $params
     */
    protected function amountOrAmounts($amount, array $params)
    {
        if (is_array($amount)) {
            $params["AMOUNTS"] = $amount;
        } else {
            $params["AMOUNT"] = $amount;
        }

        return $params;
    }
}
