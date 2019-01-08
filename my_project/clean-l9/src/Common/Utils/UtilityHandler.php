<?php

namespace Common\Utils;

class UtilityHandler
{

    static public function getIntArrFromStr($str, $splitWord)
    {
        if(empty($str) || empty($splitWord))
        {
            return array();
        }
        $arr = split($splitWord, $str);
        $result = array();
        for($i = 0; $i < count($arr); $i ++)
        {
            if(! empty($arr[$i]))
            {
                array_push($result, intval($arr[$i]));
            }
        }
        return $result;
    }

    static public function getIntStrFromStr($str, $splitWord, $newConnectWord)
    {
        if(empty($str) || empty($splitWord))
        {
            return array();
        }
        $arr = explode($splitWord, $str);
        $result = "";
        for($i = 0; $i < count($arr); $i ++)
        {
            if(! empty($arr[$i]))
            {
                $result = $result . $newConnectWord . intval($arr[$i]);
            }
        }
        $result = trim($result, $newConnectWord);
        return $result;
    }

    /**
     * 作用：将xml转为array
     */
    static public function xmlToArray($xml)
    {
        if(empty($xml))
        {
            return null;
        }
        // 将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    static public function prepareJSON($input)
    {
        $imput = mb_convert_encoding($input, 'UTF-8', 'ASCII,UTF-8,ISO-8859-1');
        if(substr($input, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF))
            $input = substr($input, 3);
        return $input;
    }
}

?>