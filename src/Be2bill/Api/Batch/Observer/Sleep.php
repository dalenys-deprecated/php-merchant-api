<?php

/**
 * Class Be2bill_Api_Batch_Observer_Sleep
 * Use it for configuring some sleep time between each transactions
 */
class Be2bill_Api_Batch_Observer_Sleep implements SplObserver
{
    protected $sleep;

    /**
     * @param integer $msec Milliseconds
     */
    public function __construct($msec)
    {
        $this->sleep = $msec;
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
        usleep($this->sleep * 1000);
    }
}