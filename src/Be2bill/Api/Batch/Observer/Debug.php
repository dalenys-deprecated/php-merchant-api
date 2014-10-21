<?php

/**
 * Class Be2bill_Api_Batch_Observer_Debug
 * Will display on console output some debug data
 */
class Be2bill_Api_Batch_Observer_Debug implements SplObserver
{
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
        $line   = $subject->getCurrentLine() + 1;
        $params = $subject->getCurrentTransactionParameters();
        $result = $subject->getCurrentTransactionResult();

        $output = "Line {$line}";

        if (isset($params['ORDERID'])) {
            $output .= " (ORDERID={$params['ORDERID']})";
        }

        $output .= ' :';

        if (isset($result['EXECCODE'])) {
            $output .= " EXECCODE={$result['EXECCODE']}";
        }

        if (isset($result['MESSAGE'])) {
            $output .= " MESSAGE={$result['MESSAGE']}";
        }


        if (isset($result['TRANSACTIONID'])) {
            $output .= " TRANSACTIONID={$result['TRANSACTIONID']}";
        }

        $output .= "\n";

        echo $output;
    }
}