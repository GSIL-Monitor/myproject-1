<?php

namespace Common\Utils;

class WeekdayHandler
{

    /*
     * 0-6
     */
    static public function GetTimestampByWeekday($week, $isZero = false)
    {
        $week = intval($week);
        if($week < 0 || $week > 6)
        {
            return null;
        }
        $currentWeek = date("w");
        $result = strtotime("-" . strval($currentWeek - $week) . " days");
        if($isZero)
        {
            $result = strtotime(date("Y-m-d", $result));
        }
        return $result;
    }

    /*
     * 1-7
     */
    static public function GetTimestampByWeekdayCN($week, $isZero = false)
    {
        $week = intval($week);
        if($week < 1 || $week > 7)
        {
            return null;
        }
        $currentWeek = date("w");
        if($currentWeek == 0)
        {
            $currentWeek = 7;
        }
        $result = strtotime("-" . strval($currentWeek - $week) . " days");
        if($isZero)
        {
            $result = strtotime(date("Y-m-d", $result));
        }
        return $result;
    }
}

?>