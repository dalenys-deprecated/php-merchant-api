<?php

/**
 * Implements batch mode (cli side)
 * @version 1.2.0
 */
class Be2bill_Api_BatchClient implements SplSubject
{
    protected $delimiter = ';';
    protected $enclosure = '"';

    protected $observers = array();

    protected $currentLine = 0;
    protected $currentTransactionParameters;
    protected $currentTransactionResult;
    protected $headers;

    /**
     * @var Be2bill_Api_DirectLinkClient
     */
    protected $api;
    protected $inputFile;
    protected $inputFd;

    public function __construct(Be2bill_Api_DirectLinkClient $api)
    {
        $this->api = $api;
    }

    public function setInputFile($file)
    {
        if (is_resource($file)) {
            $data = stream_get_meta_data($file);
            $this->inputFile = $data['uri'];
            $this->inputFd = $file;
        } else {
            $this->inputFile = $file;
            $this->inputFd   = fopen($file, 'r');
        }
    }

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
     * @return int
     */
    public function getCurrentLine()
    {
        return $this->currentLine;
    }

    /**
     * @return mixed
     */
    public function getCurrentTransactionParameters()
    {
        return $this->currentTransactionParameters;
    }

    /**
     * @return mixed
     */
    public function getCurrentTransactionResult()
    {
        return $this->currentTransactionResult;
    }

    public function getFile()
    {
        return $this->inputFile;
    }

    public function getInputFileDescriptor()
    {
        return $this->inputFd;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * @return array
     */
    protected function getCsvHeaders()
    {
        $headers = fgetcsv($this->inputFd, null, $this->delimiter, $this->enclosure);
        return $headers;
    }

    /**
     * @param $headers
     * @throws Be2bill_Api_Exception_InvalidBatchFile
     * @return array
     */
    protected function getCsvLine($headers)
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
     * @param $headers
     * @throws Be2bill_Api_Exception_InvalidBatchFile
     */
    protected function validateFileHeaders($headers)
    {
        if (in_array('IDENTIFIER', $headers)) {
            throw new Be2bill_Api_Exception_InvalidBatchFile('IDENTIFIER is not allowed in batch file');
        }
    }

    /**
     * @param $params
     * @return array
     */
    protected function prepareTransactionParameters($params)
    {
        $params['IDENTIFIER'] = $this->api->getIdentifier();
        $params               = array_filter($params);

        $params['HASH'] = $this->api->hash($params);

        return $params;
    }

    // Special methods
    public function __destruct()
    {
        fclose($this->inputFd);
    }
}
