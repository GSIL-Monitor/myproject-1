<?php
require_once 'CommonConstParameter.php';
require_once 'WebSocket.php';
class SocketHandler
{
/*
     * 获取返回结果
     */
    static public function getResultJson($infoType, $message, $data, $connectionType=CommonConstParameter::CONNECTION_TYPE_SOCKET)
    {
        if(empty($message))
        {
            return $message;
        }
    
        $apiResult = array(
                "infoType"=>$infoType,
                "message"=>$message,
                "data"=>$data
        );
        
        $result=json_encode($apiResult)."#\t#";
        
        // json编码会把中文换为unified
//         $result = preg_replace_callback(
//                 "#\\\\u([0-9a-f]{4})#i",
//                 function ($matches)
//                 {
//                     return iconv('UCS-2BE', 'UTF-8', pack('H4', $matches[1]));
//                 },
//                 $result);
        if($connectionType==CommonConstParameter::CONNECTION_TYPE_WEBSOCKET)
        {
            $ws=new WebSocket();
    	    $result=$ws->wrap($result);
        }
    
        return $result;
    }

}