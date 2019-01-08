<?php
namespace Common\Utils\Push;

include_once strval($_SERVER['DOCUMENT_ROOT']).'/BaiduPush/Channel.class.php';
use Web\BaiduPush\Channel;

class BaiduPushHandler
{
	static private  $apiKey = "IZLhp59Xs4g4TApcoNiYTm5f";
	static private  $secretKey = "xblbHCittEwdPcmnSw8lGm9DBQWpvqx4";
	static function pushMessageAndroid ($user_id,$title,$description,$customContent="")
	{
		//LogHandler::writeLog("start\r\n");
		$channel = new Channel( self::$apiKey, self::$secretKey ) ;
		//$channel = new Channel( $this->apiKey, $this->secretKey ) ;
		//推送消息到某个user，设置push_type = 1;
		//推送消息到一个tag中的全部user，设置push_type = 2;
		//推送消息到该app中的全部user，设置push_type = 3;
		$push_type = 1; //推送单播消息
		$optional[Channel::USER_ID] = $user_id; //如果推送单播消息，需要指定user
		//optional[Channel::TAG_NAME] = "xxxx";  //如果推送tag消息，需要指定tag_name
	
		//指定发到android设备
		$optional[Channel::DEVICE_TYPE] = 3;
		//指定消息类型0为消息1为通知 
		$optional[Channel::MESSAGE_TYPE] = 0;
		//通知类型的内容必须按指定内容发送，示例如下：
		$message = '{
			"title": "'.$title.'",
			"description": "'.$description.'",
			"notification_basic_style":7,
			"open_type":2,
			"url":"http://www.imscv.com",
			"custom_content":'.json_encode($customContent).'
 		}';
	
		$message_key = "msg_key";
		$ret = $channel->pushMessage ( $push_type, $message, $message_key, $optional ) ;
		if ( false === $ret )
		{
			//LogHandler::writeLog("fail\r\n");
			return "发送失败";
// 			echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
// 			echo ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
// 			echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
// 			echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
		}
		else
		{
			//LogHandler::writeLog("success\r\n");
			return "发送成功";
// 			right_output ( 'SUCC, ' . __FUNCTION__ . ' OK!!!!!' ) ;
// 			right_output ( 'result: ' . print_r ( $ret, true ) ) ;
		}
	}
}
?>