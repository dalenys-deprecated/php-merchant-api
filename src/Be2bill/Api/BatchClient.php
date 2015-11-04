<?php

/**
 * Batch client
 *
 * @package Be2bill
 * @author Jérémy Cohen Solal <jeremy@dalenys.com>
 */

/**
 * Implements batch mode (cli side)
 * @version 1.2.0
 */
class Be2bill_Api_BatchClient implements SplSubject
{
    /**
     * @var string CSV delimiter
     */
    protected $delimiter = ';';

    /**
     * @var string CSV enclosure
     */
    protected $enclosure = '"';

    /**
     * @var array Observer list
     */
    protected $observers = array();

    /**
     * @var int Processed line
     */
    protected $currentLine = 0;

    /**
     * @var array Current transaction parameters
     */
    protected $currentTransactionParameters;

    /**
     * @var array Current transaction result
     */
    protected $currentTransactionResult;

    /**
     * @var CSV headers
     */
    protected $headers;

    /**
     * @var Be2bill_Api_DirectLinkClient Be2bill API
     */
    protected $api;

    /**
     * @var string Input file
     */
    protected $inputFile;

    /**
     * @var resource Input file descriptor
     */
    protected $inputFd;

    /**
     * Instanciate
     *
     * @param Be2bill_Api_DirectLinkClient $api
     */
    public function __construct(Be2bill_Api_DirectLinkClient $api)
    {
        $this->api = $api;
    }

    /**
     * Set input file
     *
     * @param resource|string $file
     */
    public function setInputFile($file)
    {
        if (is_resource($file)) {
            $data            = stream_get_meta_data($file);
            $this->inputFile = $data['uri'];
            $this->inputFd   = $file;
        } else {
            $this->inputFile = $file;
            $this->inputFd   = fopen($file, 'r');
        }
    }

    /**
     * Process the batch
     *
     * @return bool
     */
    public function run()
    {
        $urls = $this->api->getDirectLinkUrls();

        $this->headers = $this->getCsvHeaders();
        $this->validateFileHeaders($this->headers);

        while (!feof($this->inputFd)) {
            $rawParams = $this->getCsvLine($this->headers);

            if ($rawParams) {
                $params = $this->prepareTransactionParameters($rawParams);

                $result = $this->api->requests($urls, $params);

                $this->currentTransactionParameters = $rawParams;
                $this->currentTransactionResult     = $result;

                $this->notify();
            }

            $this->currentLine++;
        }

        return true;
    }

    // Observer design pattern

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Attach an SplObserver
     * @link http://php.net/manual/en/splsubject.attach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to attach.
     * </p>
     * @return void
     */
    public function attach(SplObserver $observer)
    {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Detach an observer
     * @link http://php.net/manual/en/splsubject.detach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to detach.
     * </p>
     * @return void
     */
    public function detach(SplObserver $observer)
    {
        unset($this->observers[spl_object_hash($observer)]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Notify an observer
     * @link http://php.net/manual/en/splsubject.notify.php
     * @return void
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            /**
             * @var SplObserver
             */
            $observer->update($this);
        }
    }

    /**
     * Return current line
     *
     * @return int
     */
    public function getCurrentLine()
    {
        return $this->currentLine;
    }

    /**
     * Return current transaction parameters
     *
     * @return mixed
     */
    public function getCurrentTransactionParameters()
    {
        return $this->currentTransactionParameters;
    }

    /**
     * Return current transaction result
     *
     * @return mixed
     */
    public function getCurrentTransactionResult()
    {
        return $this->currentTransactionResult;
    }

    /**
     * Return input file name
     *
     * @return string
     */
    public function getFile()
    {
        return $this->inputFile;
    }

    /**
     * Return input file descriptor
     *
     * @return resource
     */
    public function getInputFileDescriptor()
    {
        return $this->inputFd;
    }

    /**
     * Return CSV delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Return CSV enclosure
     *
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Return CSV headers
     *
     * @return array
     */
    protected function getCsvHeaders()
    {
        $headers = fgetcsv($this->inputFd, null, $this->delimiter, $this->enclosure);
        return $headers;
    }

    /**
     * Return CSV line
     *
     * @param array $headers
     * @throws Be2bill_Api_Exception_InvalidBatchFile
     * @return array
     */
    protected function getCsvLine(array $headers)
    {
        $line = fgetcsv($this->inputFd, null, $this->delimiter, $this->enclosure);

        // Empty line
        if ($line[0] === null) {
            return false;
        } elseif ($this->headers && sizeof($line) != sizeof($this->headers)) {
            throw new Be2bill_Api_Exception_InvalidBatchFile("Invalid line");
        }

        $params = array_combine($headers, $line);
        return $params;
    }

    /**
     * Validate headers
     *
     * Should not contains IDENTIFIER and HASH
     * @param $headers
     * @throws Be2bill_Api_Exception_InvalidBatchFile
     */
    protected function validateFileHeaders(array $headers)
    {
        if (in_array('IDENTIFIER', $headers)) {
            throw new Be2bill_Api_Exception_InvalidBatchFile('IDENTIFIER is not allowed in batch file');
        } elseif (in_array('HASH', $headers)) {
            throw new Be2bill_Api_Exception_InvalidBatchFile('HASH is not allowed in batch file');
        }
    }

    /**
     * Prepare transaction parameters before sending
     *
     * Calculate hash and append identifier
     * @param $params
     * @return array
     */
    protected function prepareTransactionParameters($params)
    {
        $params['IDENTIFIER'] = $this->api->getIdentifier();
        $params               = array_filter($params);
        $params['HASH']       = $this->api->hash($params);

        return $params;
    }

    // Special methods
    /**
     * Reset resources
     */
    public function __destruct()
    {
        if (is_resource($this->inputFd)) {
            fclose($this->inputFd);
        }
    }
}
