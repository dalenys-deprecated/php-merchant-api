<?php

class Be2bill_Api_Batch_Observer_FileReport implements SplObserver
{
    protected $file;
    protected $headers;
    protected $knownResultHeaders;
    protected $inMemoryImage;

    public function __construct(SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Receive update from subject
     * @link http://php.net/manual/en/splobserver.update.php
     * @param SplSubject $subject <p>
     * The <b>SplSubject</b> notifying the observer of an update.
     * </p>
     * @return void
     */
    public function update(SplSubject $subject)
    {
        $position = $subject->getCurrentLine();
        $params   = $subject->getCurrentTransactionParameters();
        $result   = $subject->getCurrentTransactionResult();

        if ($position == 0) {
            $this->knownResultHeaders = array_keys($result);
            $this->headers            = array_unique(array_merge(array_keys($params), $this->knownResultHeaders));

            sort($this->headers);

            // Write headers
            $this->file->fputcsv($this->headers);
        }

        $newLine               = array_merge($params, $result);
        $this->inMemoryImage[] = $newLine;

        // Search for new header keys
        $newKeys = array_diff(array_keys($result), $this->knownResultHeaders);

        // Search for missing header keys
        $missingKeys = array_diff($this->knownResultHeaders, array_keys($result));

        // Set to empty missing keys values so fputcsv will generate empty columns
        foreach ($missingKeys as $missingKey) {
            $newLine[$missingKey] = '';
        }

        // Rewrite full memory image and file
        if ($newKeys) {
            $this->file->rewind();

            // Add new headers
            $this->headers            = array_unique(array_merge($this->headers, $newKeys));
            $this->knownResultHeaders = array_unique(array_merge($this->knownResultHeaders, $newKeys));

            // Rewrite headers
            sort($this->headers);
            $this->file->fputcsv($this->headers);

            // Re -dump top file from in memory image
            foreach ($this->inMemoryImage as $newLine) {
                foreach ($this->knownResultHeaders as $header) {
                    // The new header was not present for this line
                    if (!isset($newLine[$header])) {
                        // Set to empty so fputcsv will generate empty columns
                        $newLine[$header] = '';
                    }
                }

                ksort($newLine);
                $this->file->fputcsv($newLine);
            }
        } else {
            ksort($newLine);
            $this->file->fputcsv($newLine);
        }
    }
}