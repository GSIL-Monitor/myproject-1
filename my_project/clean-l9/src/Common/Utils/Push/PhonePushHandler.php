<?php
namespace Common\Utils\Push;

use Common\Utils\ConfigHandler;
use Common\Utils\LogHandler;
class PhonePushHandler
{
	static public function iosMessagePush($alertMessageContent,$messageCount,$deviceToken,$customContent="")
	{
		//$deviceToken= 'deda18736bc478bfdc6cbfbddb3d95bd3039bd6d76898acd93dfc64562df8fd0'; //没有空格
		$body = array(
		        "aps" => array(
    				"alert" => $alertMessageContent,
    				"badge" => $messageCount,
    				"sound"=>'default'
		          ),
		        "custom_content"=>$customContent
		);
		
		//推送方式，包含内容和声音
		$ctx = stream_context_create();
		//如果在Windows的服务器上，寻找pem路径会有问题，路径修改成这样的方法：
		//$pem = strval($_SERVER['DOCUMENT_ROOT'])."/data/"."apns-dev.pem";
		//stream_context_set_option($ctx,"ssl","local_cert",$pem);
		//linux 的服务器直接写pem的路径即可
		$pushUrl=ConfigHandler::getCommonConfig("iosPushUrl");
		$pushCertificate=ConfigHandler::getCommonConfig("iosPushCertificate");
		stream_context_set_option($ctx,"ssl","local_cert",$pushCertificate);
		$certPassword = "apple";
		stream_context_set_option($ctx, 'ssl', 'passphrase', $certPassword);
		//此处有两个服务器需要选择，如果是开发测试用，选择第二名sandbox的服务器并使用Dev的pem证书，如果是正是发布，使用Product的pem并选用正式的服务器
		$fp = stream_socket_client($pushUrl, $err, $errStr, 60, STREAM_CLIENT_CONNECT, $ctx);
		if (!$fp) {
			LogHandler::writeLog("@发送失败，错误原因：".$err." ".$errStr."\r\n","IosPush");
			return "@发送失败，错误原因：".$err." ".$errStr;
		}
		$payload = json_encode($body);
		$msg = chr(0) . pack("n",32) . pack("H*", str_replace(' ', '', $deviceToken)) . pack("n",strlen($payload)) . $payload;
		fwrite($fp, $msg);
		fclose($fp);
		LogHandler::writeLog("发送成功","IosPush");
		return "发送成功";
	}
}
?>