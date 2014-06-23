<?php

/**
 * Implements Be2bill payment API
 * @version 1.0.0
 */
class Be2bill_Api_FormClient
{
    const API_VERSION = '2.0';

    // Credentials
    protected $identifier = null;
    protected $password = null;

    // Internals

    /**
     * @var Be2bill_Api_Renderer_Renderable
     */
    protected $renderer = null;

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
        Be2bill_Api_Renderer_Renderable $renderer,
        Be2bill_Api_Hash_Hashable $hash
    ) {
        $this->setCredentials($identifier, $password);

        $this->renderer = $renderer;
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
     * @param array         $htmlOptions An array of HTML attributes to add to the submit or form button
     * (allowing to change name, style, class attribute etc.).
     * Example:
     * <code>
     * $htmlOptions['SUBMIT'] = array('class' => 'my_class');
     * $htmlOptions['FORM'] = array('class' => 'my_form', 'target' => 'my_target');
     * </code>
     * @param array         $options Others be2bill options. See Be2bill documentation for more information
     * (3DS, CREATEALIAS, etc.)
     * @return string The HTML output to display
     */
    public function buildPaymentFormButton(
        $amount,
        $orderId,
        $clientIdentifier,
        $description,
        array $htmlOptions = array(),
        array $options = array()
    ) {
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
     * @param array        $htmlOptions An array of HTML attributes to add to the submit or form button
     * (allowing to change name, style, class attribute etc.).
     * Example:
     * <code>
     * $htmlOptions['SUBMIT'] = array('class' => 'my_class');
     * $htmlOptions['FORM'] = array('class' => 'my_form', 'target' => 'my_target');
     * </code>
     * @param        array $options Others be2bill options. See Be2bill documentation for more information
     * (3DS, CREATEALIAS etc.)
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
    ) {
        $params = $options;

        $params["AMOUNT"] = $amount;

        return $this->buildProcessButton(
            'authorization',
            $orderId,
            $clientIdentifier,
            $description,
            $htmlOptions,
            $params
        );
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
    ) {
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
}
