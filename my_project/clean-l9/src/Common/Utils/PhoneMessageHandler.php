<?php
namespace Common\Utils;
use Aliyun\DySDKLite\Sms\SmsApi;

include_once strval($_SERVER['DOCUMENT_ROOT']) . '/aliyun-php-sdk-sms/aliyun-php-sdk-core/Config.php';
include_once strval($_SERVER['DOCUMENT_ROOT']) . '/aliyun-php-sdk-sms/aliyun-dysms-php-sdk-lite/SmsApi.php';

use Common\Utils\LogHandler;

class PhoneMessageHandler {
	//定义是否检测请求来源的开关常量
	const ENABLE_CHECKREFER = TRUE;

	public static function VerifyWebCodeNew($phone, $code, $time = 10) {

		//来源判断
		$refWhiteList = array(
			'^https:\/\/www.xiaogou111.com\/',
			'^https:\/\/admin.xiaogou111.com\/',
		);

		// if(!self::checkrefer($refWhiteList)){
		//     return false;
		// }

		$sms = new SmsApi("LTAIeFmj68kYBiOv", "GY1R2MtCym5UwvhWHNujxGkpPRRUGO"); // 请参阅 https://ak-console.aliyun.com/ 获取AK信息

		$response = $sms->sendSms(
			"小狗电器", // 短信签名
			"SMS_141615465", // 短信模板编号
			$phone, // 短信接收者
			Array( // 短信模板中字段的值
				"code" => $code,
				//"time"=>$time
			)
		);

		if ($response->Code == "OK") {
			return true;
		} else {
			LogHandler::writeLog(json_encode($response), "test/response");
			return false;
		}

	}

	public static function getCode($len = 6) {
		$str = '';
		for ($i = 0; $i < $len; $i++) {
			$str .= rand(0, 9);
		}
		return $str;
	}

	/**
	 * 网站请求,校验来源地址
	 *
	 * @author
	 * @param string $refWhiteList 域名白名单
	 * @return bool
	 */
	public static function checkrefer($refWhiteList) {
		//判断refer校验开关,是否开启
		if (!self::ENABLE_CHECKREFER || self::ENABLE_CHECKREFER == false) {
			return true;
		} else {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$ref = $_SERVER['HTTP_REFERER'];
				if (strpos($ref, 'http://') !== 0 && strpos($ref, 'https://') !== 0) {
					$ref = 'http://' . $ref;
				}
				foreach ($refWhiteList as $item) {
					if (preg_match("/{$item}/i", $ref)) {
						return true;
					}
				}
				return false;
			} else {
				return false;
			}
		}
	}

	/**
	 * 发起一个post请求到指定接口
	 *
	 * @param string $api
	 *            请求的接口
	 * @param array $params
	 *            post参数
	 * @param int $timeout
	 *            超时时间
	 * @return string 请求结果
	 */
	public static function postRequest($api, array $params = array(), $timeout = 30) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api);
		// 以返回的形式接收信息
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 设置为POST方式
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		// 不验证https证书
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
			'Accept: application/json',
		));
		// 发送数据
		$response = curl_exec($ch);
		// 不要忘记释放资源
		curl_close($ch);
		return $response;
	}
}

?>