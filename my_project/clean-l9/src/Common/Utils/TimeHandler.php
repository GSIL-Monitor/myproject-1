<?php

namespace Common\Utils;

class TimeHandler
{

    static function getAge($strTime)
    {
        $soureTime = strtotime($strTime); // int strtotime ( string $time [, int $now ] )
        $year = date('Y', $soureTime);
        if(($month = (date('m') - date('m', $soureTime))) < 0)
        {
            $year ++;
        }
        else if($month == 0 && date('d') - date('d', $soureTime) < 0)
        {
            $year ++;
        }
        return date('Y') - $year;
    }
}

?>