<?php

namespace Common\Utils;

class RandomHander
{

    static function generateTransactionSequenceNumber()
    {
        return date('ymd') . substr(time(), - 5) . substr(microtime(), 2, 5);
    }
}

?>