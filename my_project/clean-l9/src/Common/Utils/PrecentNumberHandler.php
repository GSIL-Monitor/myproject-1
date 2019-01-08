<?php

namespace Common\Utils;

class PrecentNumberHandler
{

    /*
     * 用户Id转推荐码
     */
    static public function presentUserIdToPresentNumber($userId)
    {
        $firstTable = array(
                'A',
                'Y',
                'L',
                'C',
                'F',
                'G',
                'K',
                'M',
                'Z',
                'B',
                'N',
                'S',
                'P',
                'R',
                'I',
                'D',
                'H',
                'U',
                'J',
                'E' 
        );
        $secondTable = array(
                'O',
                'T',
                'V',
                'X',
                'Q',
                'W' 
        );
        
        $userId = PrecentNumberHandler::strToArray($userId);
        $presentNumber = array();
        for($i = 0; $i < count($userId); $i ++)
        {
            $key = isset($userId[$i + 1]) ? $userId[$i] + $userId[$i + 1] : $userId[$i];
            $presentNumber[] = $firstTable[$key];
        }
        
        while(count($presentNumber) < 6)
        {
            $len = count($presentNumber);
            $len % 2 == 0 ? array_unshift($presentNumber, $secondTable[$len]) : array_push($presentNumber, $secondTable[$len]);
        }
        
        return implode('', $presentNumber);
    }

    /*
     * 用户Id转推荐码
     */
    static public function presentNumberToPresentUserId($presentNumber)
    {
        $firstTable = array(
                'A',
                'Y',
                'L',
                'C',
                'F',
                'G',
                'K',
                'M',
                'Z',
                'B',
                'N',
                'S',
                'P',
                'R',
                'I',
                'D',
                'H',
                'U',
                'J',
                'E' 
        );
        $secondTable = array(
                'O',
                'T',
                'V',
                'X',
                'Q',
                'W' 
        );
        
        $presentNumber = strtoupper($presentNumber);
        
        $temp = array();
        for($i = 0; $i < strlen($presentNumber); $i ++)
        {
            $subStr = substr($presentNumber, $i, 1);
            if(! in_array($subStr, $secondTable))
            {
                $temp[] = array_search($subStr, $firstTable);
            }
        }
        
        $j = 0;
        $userId = array();
        for($i = count($temp) - 1; $i >= 0; $i --)
        {
            $j = $temp[$i] - $j;
            array_unshift($userId, $j);
        }
        
        return intval(implode('', $userId));
    }

    static private function strToArray($str)
    {
        $strArray = array();
        for($i = 0; $i < strlen($str); $i ++)
        {
            $strArray[] = substr($str, $i, 1);
        }
        return $strArray;
    }
}