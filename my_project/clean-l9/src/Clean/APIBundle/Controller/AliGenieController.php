<?php

namespace Clean\APIBundle\Controller;

use Clean\APIBundle\Controller\BaseController;
use Common\Utils\AlexaHandler;
use Common\Utils\Crypt\CheckCryptHandler;
use Common\Utils\LogHandler;
use Symfony\Component\HttpFoundation\Response;

class AliGenieController extends BaseController {

	//正式
	public function transmitDataAction() {

		$postData = file_get_contents('php://input');
		LogHandler::writeLog($postData, "AliGenie/transmitData");

		//$postData = file_get_contents("/mnt/www/upload/web-log/AliGenie/transmitData/20181022.txt");

		$temRes = json_decode($postData, true);
		$header = $temRes["header"];
		$payload = $temRes["payload"];
		$accessToken = $payload["accessToken"];
		if (!$accessToken) {
			return new Response("Please authenticate user information first");
		}

		//获取名字
		$intentName = $header["name"];
		//$intentName = "TurnOn";

		//获取userId
		$lmcu = $this->get("library_model_clean_userinfo");
		$userInfo = $lmcu->getEntityByAuthenticationToken($accessToken);
		$userId = $userInfo->getUserId();
		$nowSn = $userInfo->getNowSn();
		if ($nowSn && strlen($nowSn) == 16) {
			//获取机器消息
			$lmcm = $this->get("library_model_clean_machine");
			$machineInfo = $lmcm->getMachineBySn($nowSn);
		}

		switch ($intentName) {
		case "DiscoveryDevices":
			$header = array(
				"namespace" => $header["namespace"],
				"name" => "DiscoveryDevicesResponse",
				"messageId" => $header["messageId"],
				"payLoadVersion" => $header["payLoadVersion"],
			);

			$deviceId = $machineInfo->getMachineId();
			$machineName = $machineInfo->getMachineName() ? $machineInfo->getMachineName() : $nowSn;

			//需要判断扫地机是否在线
			$properties[] = array(
				"name" => "powerstate", // 电源状态
				"value" => "on",
			);
			$devices[] = array(
				"deviceId" => $deviceId,
				"deviceName" => $machineName,
				"deviceType" => "roboticvacuum",
				"brand" => "R55 Cyclone",
				"model" => "小狗智能",
				"icon" => "https://file.xiaogou111.com/clean-appFile/160.jpg",
				"properties" => $properties,
				"actions" => array("TurnOn", "TurnOff", "Pause", "Continue", "Query"),
			);

			$res = array(
				"header" => $header,
				"payload" => array("devices" => $devices),
			);
			break;
		case "TurnOn":
			$res = array(
				"infoType" => 21005,
				"connectionType" => 1,
				"deviceType" => 4,
			);
			$res["dInfo"] = array(
				"ts" => time(),
				"userId" => $userId,
			);
			$res["data"] = array(
				"sn" => $nowSn,
				"mode" => "smartClean",
			);

			$res = json_encode($res, JSON_UNESCAPED_SLASHES) . "#\t#";
			$res = str_replace(" ", "+", $res);
			$res = AlexaHandler::swooleClient($res, 9501);

			$header = array(
				"namespace" => $header["namespace"],
				"name" => "TurnOnResponse",
				"messageId" => $header["messageId"],
				"payLoadVersion" => $header["payLoadVersion"],
			);

			$payload = array("deviceId" => $payload["deviceId"]);

			$res = array(
				"header" => $header,
				"payload" => $payload,
			);

			break;
		case "Continue":
			$res = array(
				"infoType" => 21017,
				"connectionType" => 1,
				"deviceType" => 4,
			);
			$res["dInfo"] = array(
				"ts" => time(),
				"userId" => $userId,
			);
			$res["data"] = array(
				"sn" => $nowSn,
				"cmd" => "continue",
			);

			$res = json_encode($res, JSON_UNESCAPED_SLASHES) . "#\t#";
			$res = str_replace(" ", "+", $res);
			$res = AlexaHandler::swooleClient($res, 9501);

			$header = array(
				"namespace" => $header["namespace"],
				"name" => "ContinueResponse",
				"messageId" => $header["messageId"],
				"payLoadVersion" => $header["payLoadVersion"],
			);

			$payload = array("deviceId" => $payload["deviceId"]);

			$res = array(
				"header" => $header,
				"payload" => $payload,
			);

			break;
		case "Pause":
			$res = array(
				"infoType" => 21017,
				"connectionType" => 1,
				"deviceType" => 4,
			);
			$res["dInfo"] = array(
				"ts" => time(),
				"userId" => $userId,
			);
			$res["data"] = array(
				"sn" => $nowSn,
				"cmd" => "pause",
			);

			$res = json_encode($res, JSON_UNESCAPED_SLASHES) . "#\t#";
			$res = str_replace(" ", "+", $res);
			$res = AlexaHandler::swooleClient($res, 9501);

			$header = array(
				"namespace" => $header["namespace"],
				"name" => "PauseResponse",
				"messageId" => $header["messageId"],
				"payLoadVersion" => $header["payLoadVersion"],
			);

			$payload = array("deviceId" => $payload["deviceId"]);

			$res = array(
				"header" => $header,
				"payload" => $payload,
			);

			break;
		case "TurnOff":
			$res = array(
				"infoType" => 21017,
				"connectionType" => 1,
				"deviceType" => 4,
			);
			$res["dInfo"] = array(
				"ts" => time(),
				"userId" => $userId,
			);
			$res["data"] = array(
				"sn" => $nowSn,
				"cmd" => "pause",
			);

			$res = json_encode($res, JSON_UNESCAPED_SLASHES) . "#\t#";
			$res = str_replace(" ", "+", $res);
			$res = AlexaHandler::swooleClient($res, 9501);

			$header = array(
				"namespace" => $header["namespace"],
				"name" => "TurnOffResponse",
				"messageId" => $header["messageId"],
				"payLoadVersion" => $header["payLoadVersion"],
			);

			$payload = array("deviceId" => $payload["deviceId"]);

			$res = array(
				"header" => $header,
				"payload" => $payload,
			);

			break;
		default:
			$res = array(
				"infoType" => 21017,
				"connectionType" => 2,
				"deviceType" => 3,
			);
			$data = array(
				"userId" => $userId,
				"cmd" => "continue",
			);

			$res["data"] = CheckCryptHandler::encrypt(json_encode($data));
			$data = json_encode($res) . "#\t#";
			break;

		}

		return new Response($this->getJson("N00000", "成功", $res));
	}

	public function loginAuthenticationAction() {
		try
		{
			$loginName = $this->requestParameter("loginName");
			$password = $this->requestParameter("password");

			if (empty($loginName)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}
			if (empty($password) && strlen($password) != 32) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$password = md5(md5($password));
			$userInfo = $lmcu->getUserInfoByLogin($loginName, $password);

			if (empty($userInfo)) {

				$loginName = "+86&nbsp;" . $loginName;
				$userInfo = $lmcu->getUserInfoByLogin($loginName, $password);
				if (empty($userInfo)) {
					return new Response($this->getAPIResultJson("E02000", "用户信息错误", ""));
				}

			}

			$state = $this->requestParameter("state");
			$redirect_uri = $this->requestParameter("redirect_uri");
			$client_id = $this->requestParameter("client_id");
			if (empty($state) || empty($redirect_uri)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$response_type = $this->requestParameter("response_type");

			if ($client_id != md5("clean-robot")) {
				return new Response($this->getAPIResultJson("E02000", "认证失败", ""));
			}
			$redirect_uri = urldecode($redirect_uri);

			$log = array(
				"redirect_uri" => $redirect_uri,
				"state" => $state,
				"client_id" => $client_id,
				"response_type" => $response_type,
			);
			LogHandler::writeLog(json_encode($log), "AliGenie");

			$code = md5("code@" . time() . rand());
			$userInfo->setAuthenticationToken($code);
			$lmcu->editEntity($userInfo);
			$url = $redirect_uri . '&state=' . $state . '&client_id=' . $client_id . '&response_type=' . $response_type . "&code=" . $code;
			return new Response($this->getAPIResultJson("N00000", "获取code成功", $url));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
		return new Response($this->getAPIResultJson("E02000", "认证失败", ""));
	}

	public function getTokenAction() {
		try
		{
			$grant_type = $this->requestParameter("grant_type");
			$client_id = $this->requestParameter("client_id");
			$client_secret = $this->requestParameter("client_secret");
			$code = $this->requestParameter("code");
			$redirect_uri = $this->requestParameter("redirect_uri");

			if (empty($grant_type) || empty($client_id) || empty($client_secret) || empty($code)) {
				return new Response($this->getJson("E02000", "数据填写不完整", ""));
			}

			if ($client_id != md5("clean-robot") || $client_secret != md5("inmotion")) {
				return new Response($this->getJson("E02000", "认证失败", ""));
			}

			if ($grant_type == "refresh_token") {
				$code = $this->requestParameter("refresh_token");
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfo = $lmcu->getEntityByAuthenticationToken($code);

			if (empty($userInfo)) {
				return new Response($this->getJson("E02000", "认证失败", ""));
			}

			$token = md5($userInfo->getUserId() . "@" . time() . rand());
			$userInfo->setAuthenticationToken($token);
			$lmcu->editEntity($userInfo);

			$data = array(
				"access_token" => $token,
				"refresh_token" => $token,
				"expires_in" => 17600000,
			);

			LogHandler::writeLog(json_encode($data), "AliGenie/token");

			return new Response($this->getJson("N00000", "获取code成功", $data));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getJson("E01000", "服务器错误", ""));
		}
		return new Response($this->getJson("E02000", "认证失败", ""));
	}

	private function getJson($code, $message, $data) {
		if ($code == "N00000") {
			$result = json_encode($data);
		} else {
			$res = array("error" => $code, "error_description" => $message);
			$result = json_encode($res);
		}
		return $result;
	}

	public function responseAction() {

		if (!$this->validateCleanMachine()) {
			return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
		}

		$data = $this->getParameter("data");
		if (empty($data)) {
			return new Response($this->getAPIResultJson("E02000", "暂无数据", ""));
		}

		LogHandler::writeLog($data . "\r\n", "https");

		$temRes = json_decode($data);
		$temRes["deviceType"] = 3;

		$resData = json_encode($res) . "#\t#";

		$res = AlexaHandler::swooleClient($resData);

		return new Response($this->getAPIResultJson("N00000", "success", ""));
	}

}
?>