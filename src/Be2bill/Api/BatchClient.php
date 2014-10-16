<?php

/**
 * Implements batch mode (cli side)
 * @version 1.2.0
 */
class Be2bill_Api_BatchClient
{
    protected $delimiter = ';';
    protected $enclosure = '"';
    protected $escape = '\\';

    /**
     * @var Be2bill_Api_DirectLinkClient
     */
    protected $api;

    /**
     * @var SplFileObject
     */
    protected $inputFile;

    public function __construct(Be2bill_Api_DirectLinkClient $api)
    {
        $this->api = $api;
    }

    public function setInputFile(SplFileObject $file)
    {
        $this->inputFile = $file;
        $this->inputFile->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
    }

    public function run()
    {
        $headers = $this->getCsvHeaders();

        $this->validateFileHeaders($headers);

        while (!$this->inputFile->eof()) {
            $params = $this->getCsvLine($headers);

            $params['IDENTIFIER'] = $this->api->getIdentifier();
            $params['HASH']       = $this->api->hash($params);

            $this->api->requests($this->api->getDirectLinkUrls(), $params);
        }

        return true;
    }

    /**
     * @return array
     */
    protected function getCsvHeaders()
    {
        $headers = $this->inputFile->fgetcsv();
        return $headers;
    }

    /**
     * @param $headers
     * @return array
     */
    protected function getCsvLine($headers)
    {
        $line   = $this->inputFile->fgetcsv();
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
}
