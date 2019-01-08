<?php

namespace Clean\APIBundle\Controller;

use Clean\APIBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\MachineCleanRecordEntity;
use Clean\LibraryBundle\Entity\MachineDataEntity;
use Clean\LibraryBundle\Entity\MachineKeyEntity;
use Clean\LibraryBundle\Entity\MachineLogEntity;
use Clean\LibraryBundle\Entity\MachineMapEntity;
use Clean\LibraryBundle\Entity\SystemMessageEntity;
use Clean\LibraryBundle\Entity\UserMachineEntity;
use Common\Utils\AlexaHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\Crypt\CheckCryptHandler;
use Common\Utils\Crypt\RSAHandler;
use Common\Utils\File\UploadFileHandler;
use Common\Utils\LogHandler;
use Symfony\Component\HttpFoundation\Response;

class RobotController extends BaseController {

	//主动上传数据
	public function broadcastAction() {
		$startTime = $this->msectime();

		if (!$this->validateCleanRobot()) {
			return new Response($this->getRobotResultJson("102", "你的cookies过期啦", ""));
		}

		$sn = $this->requestParameter("sn");
		$ts = intval($this->requestParameter("ts")); // 任务Id 后续用

		$data = $_REQUEST["data"];

		if (!$sn || !$data) {
			return new Response($this->getRobotResultJson("100", "缺少参数", ""));
		}

		$temData = json_decode($data, true);

		if ($temData["infoType"] == 20002) {
			//地图数据存起来
			$mapData = str_replace(" ", "+", $data);
			$rem = $this->saveMapData($mapData, $sn);

			//先多发一条消息
			//推送有新地图的消息
			$mapPushData = array(
				"infoType" => 23002,
				"connectionType" => 1,
				"deviceType" => 3,
				"sn" => $sn,
			);
			$mapPushData["data"] = array(
				"newMap" => 1,
			);

			$mapPushData["connectionType"] = 1;
			$mapPushData["data"]["sn"] = $sn;
			$mapPushData["dInfo"] = array("ts" => $ts, "sn" => $sn);

			$mapPushData = json_encode($mapPushData, JSON_UNESCAPED_SLASHES) . "#\t#";
			$mapPushData = str_replace(" ", "+", $mapPushData);
			$res = AlexaHandler::swooleClient($mapPushData, 9501);

			$endTime = $this->msectime();
			$timeCount = $endTime - $startTime;

			if ($timeCount > 100) {
				LogHandler::writeLog("time:" . $timeCount . "\r\n" . "ts:" . $ts . "\r\n", "robot/broadcast-time");
			}

			return new Response($this->getRobotResultJson("0", "success", $timeCount));
		}

		$temData["connectionType"] = 1;
		$temData["deviceType"] = 3;
		$temData["sn"] = $sn;
		$temData["data"]["sn"] = $sn;
		$temData["dInfo"] = array("ts" => $ts, "sn" => $sn);

		//$temData["data"] = CheckCryptHandler::encrypt(json_encode($temData["data"]));
		$resData = json_encode($temData, JSON_UNESCAPED_SLASHES) . "#\t#";
		$resData = str_replace(" ", "+", $resData);

		//LogHandler::writeLog($resData."\r\n", "https");

		$res = AlexaHandler::swooleClient($resData, 9501);

		LogHandler::writeLog(json_encode($res) . "\r\n" . $ts . "\r\n", "robot/broadcast");

		$endTime = $this->msectime();
		$timeCount = $endTime - $startTime;

		if ($timeCount > 100) {
			LogHandler::writeLog("time:" . $timeCount . "\r\n" . "ts:" . $ts . "\r\n", "robot/broadcast-time");
		}

		return new Response($this->getRobotResultJson("0", "success", $timeCount));
	}

	//根据命令回应的数据
	public function responseAction() {
		$startTime = $this->msectime();

		if (!$this->validateCleanRobot()) {
			return new Response($this->getRobotResultJson("102", "你的cookies过期啦", ""));
		}

		$sn = $this->requestParameter("sn");
		$ts = intval($this->requestParameter("ts")); // 任务Id 后续用
		$userId = intval($this->requestParameter("userId"));
		$data = $_REQUEST["data"];

		if (!$sn || !$data) {
			return new Response($this->getRobotResultJson("100", "缺少参数", ""));
		}

		$temData = json_decode($data, true);

		$temData["connectionType"] = 1;
		$temData["deviceType"] = 3;
		$temData["sn"] = $sn;
		$temData["data"]["sn"] = $sn;
		$temData["dInfo"] = array("ts" => $ts, "sn" => $sn);

		if ($userId) {
			$temData["dInfo"]["userId"] = $userId;
		}

		if ($temData["infoType"] == 21011) {
			$pointCounts = $temData["data"]["pointCounts"];
			if ($pointCounts > 500) {
				//把路径信息存起来
				$this->saveMapPathData($data, $sn, $ts);
				$temData["dInfo"]["isHttp"] = 1;
				$temData["dInfo"]["sn"] = $sn;
				$temData["data"] = "";
			}

		}

		//$temData["data"] = CheckCryptHandler::encrypt(json_encode($temData["data"]));
		$resData = json_encode($temData, JSON_UNESCAPED_SLASHES) . "#\t#";
		//$resData = str_replace(" ", "+", $resData);

		$res = AlexaHandler::swooleClient($resData, 9501);
		LogHandler::writeLog(json_encode($res) . "\r\n" . $ts . "\r\n", "robot/response");

		$endTime = $this->msectime();
		$timeCount = $endTime - $startTime;

		if ($timeCount > 100) {
			LogHandler::writeLog("time:" . $timeCount . "\r\n" . "ts:" . $ts . "\r\n", "robot/response-time");
		}

		return new Response($this->getRobotResultJson("0", "success", $timeCount));
	}

	//获取服务端ip地址
	public function getIPListAction() {

		$companyId = $this->requestParameter("companyId");

		$data = array();
		$data["addr_list"] = array(

			array(
				"ip" => "39.107.127.173",
				"port" => 9501,
			),
		);

		return new Response($this->getRobotResultJson("0", "success", $data));

	}

	//同步数据
	public function loginAction() {

		if (!$this->validateCleanRobot()) {
			return new Response($this->getRobotResultJson("102", "你的cookies过期啦", ""));
		}

		$sn = $this->requestParameter("sn");
		$hardware = $this->requestParameter("mcuVer"); //硬件版本号
		$version = $this->requestParameter("version"); //固件版本号
		$companyId = intval($this->requestParameter("companyId"));
		$companyId = 15;

		if (empty($sn)) {
			return new Response($this->getRobotResultJson("100", "缺少参数", ""));
		}

		$lmcm = $this->get("library_model_clean_machine");
		$machineEntity = $lmcm->isExistSn($sn);
		if (!$machineEntity) {
			return new Response($this->getRobotResultJson("103", "该sn无效", ""));
		}

		if ($companyId > 0 || $companyId == -1) {
			$machineEntity->setCompanyId($companyId);
		}

		$machineEntity->setHardware($hardware);
		$machineEntity->setVersion($version);
		$lmcm->editEntity($machineEntity);

		$res = array();
		$res["time"] = time();

		//获取sn是否有用户绑定
		$lmcum = $this->get("library_model_clean_usermachine");
		if ($lmcum->getEntityBySn($sn)) {
			$res["bindStatus"] = 1;
		} else {
			$res["bindStatus"] = 0;
		}

		return new Response($this->getRobotResultJson("0", "success", $res));
	}

	//注册
	public function registerAction() {
		$sn = $this->requestParameter("sn");
		$signature = $this->requestParameter("sig"); //验证签名

		if (!$sn || !$signature) {
			return new Response($this->getRobotResultJson("100", "缺少参数", ""));
		}

		$lmcmk = $this->get("library_model_clean_machinekey");
		$machineKeyInfo = $lmcmk->getMachineKeyInfoBySn($sn);
		if ($machineKeyInfo) {
			$key = $machineKeyInfo->getPrivateKey();
		} else {
			$key = '';
		}
		$str = RSAHandler::rasDecrypt($signature, $key);

		//先把签名存下来
		//LogHandler::writeLog($str."\r\n".$sn."\r\n", "https-250");
		if (strpos($str, $sn) === false) {
			return new Response($this->getRobotResultJson("104", "签名认证失败", ""));
		}

		$res = array();
		//获取sn是否有用户绑定
		$lmcum = $this->get("library_model_clean_usermachine");
		if ($lmcum->getEntityBySn($sn)) {
			$res["bindStatus"] = 1;
		} else {
			$res["bindStatus"] = 0;
		}
		$pushKey = $this->createAESKey(16);

		//更新秘钥
		if (!$machineKeyInfo) {
			$entity = new MachineKeyEntity();
			$entity->setPushKey($pushKey);
			$entity->setSn($sn);
			$lmcmk->addEntity($entity);
		} else {
			$machineKeyInfo->setPushKey($pushKey);
			$lmcmk->editEntity($machineKeyInfo);
		}
		$this->addMachineKey($sn, $pushKey);

		$res["session"] = $pushKey;
		$temStr = $this->createAESKey(6) . ":" . time();
		$res["cookies"] = CheckCryptHandler::encrypt($temStr);

		return new Response($this->getRobotResultJson("0", "成功", $res));
	}

	//扫地机配网绑定
	public function bindAction() {
		//绑定删除所有用户，自己是管理者
		if (!$this->validateCleanRobot()) {
			return new Response($this->getRobotResultJson("102", "你的cookies过期啦", ""));
		}

		$userId = intval($this->requestParameter("userId"));
		$sn = $this->requestParameter("sn");

		if (!is_int($userId) || $userId <= 0) {
			return new Response($this->getRobotResultJson("100", "缺少参数", ""));
		}

		if (empty($sn)) {
			return new Response($this->getRobotResultJson("100", "缺少参数", ""));
		}

		$lmcm = $this->get("library_model_clean_machine");
		$snInfo = $lmcm->isExistSn($sn);
		if (!$snInfo) {
			return new Response($this->getRobotResultJson("103", "该sn无效", ""));
		}
		$lmcum = $this->get("library_model_clean_usermachine");

		//删除该sn所有绑定者
		$entityList = $lmcum->getEntityBySn($sn);
		$result = $lmcum->deleteAllUserMachine($sn);
		$lmcsm = $this->get("library_model_clean_systemmessage");
		$lmcu = $this->get("library_model_clean_userinfo");
		for ($i = 0; $i < count($entityList); $i++) {
			$toUserId = $entityList[$i]->getUserId();
			//修改订阅SN
			$userNewEntity = $lmcu->getEntity($toUserId);
			if ($userNewEntity && $userNewEntity->getNowSn() == $sn) {
				$newSn = "";
				$userNewEntity->setNowSn($newSn);
				$lmcu->editEntity($userNewEntity);
			}
			//插入消息表
			$entity = new SystemMessageEntity();
			$entity->setCompanyId($userNewEntity->getCompanyId());
			$entity->setTitle("解绑扫地机");
			$content = "您已经解绑扫地机,SN:" . $sn;
			$entity->setMessageContent($content);
			$entity->setMessageType("1");
			$entity->setFromUserId($userId);
			$entity->setToUserId($toUserId);
			$systemMessageId = $lmcsm->addEntity($entity);
		}

		$userSnInfo = $lmcum->isExistUserSn($sn, $userId);
		if ($userSnInfo) {
			$userSnInfo->setUserType(1);
			$lmcum->editEntity($userSnInfo);
		} else {
			$UserMachineEntity = new UserMachineEntity();
			$UserMachineEntity->setUserId($userId);
			$UserMachineEntity->setUserType(1);
			$UserMachineEntity->setSn($sn);
			$userMachineId = $lmcum->addEntity($UserMachineEntity);
		}

		//修改用户订阅sn
		$userInfoEntity = $lmcu->getEntity($userId);

		$historyNowSn = $userInfoEntity->getNowSn();

		$userInfoEntity->setNowSn($sn);

		//修改用户companyId
		if ($snInfo->getCompanyId() > 0) {
			$userInfoEntity->setCompanyId($snInfo->getCompanyId());
		}
		//zhi
		$userInfoEntity->setIsStartBind(1);
		$lmcu->editEntity($userInfoEntity);

		if ($historyNowSn) {
			$this->deleteUserBySn($historyNowSn);
		}

		$this->addUserBySn($sn);

		//通知APP绑定成功
		$pushData = array(
			"infoType" => 23003,
			"connectionType" => 1,
			"deviceType" => 3,
			"sn" => $sn,
		);
		$pushData["data"] = array(
			"bindStatus" => 1,
			"sn" => $sn,
		);
		$pushData["dInfo"] = array("ts" => time(), "userId" => $userId);
		$pushData = json_encode($pushData, JSON_UNESCAPED_SLASHES) . "#\t#";
		$res = AlexaHandler::swooleClient($pushData, 9501);

		for ($i = 0; $i < 3; $i++) {
			//多推送几次成功给APP
			sleep(1);
			$res = AlexaHandler::swooleClient($pushData, 9501);
		}

		return new Response($this->getRobotResultJson("0", "成功", $sn));
	}

	//上传清扫记录
	public function uploadCleanFileAction() {
		try {
			if (!$this->validateCleanRobot()) {
				return new Response($this->getRobotResultJson("102", "你的cookies过期啦", ""));
			}

			$filePath = ConfigHandler::getCommonConfig("cleanPath");
			$fileRes = UploadFileHandler::requestUpload("cleanFile", $filePath, true);
			if (!is_array($fileRes) && strpos($fileRes, "@") > 0) {
				return new Response($this->getRobotResultJson("200", "fail", ""));
			}
			//扫地机上传错误日志
			if (is_array($fileRes) && isset($fileRes[3]) && $fileRes[3] == "log") {
				$res = array();
				$content = $fileRes[0];
				$temArr = explode("|", substr($content, 1));
				for ($i = 0; $i < count($temArr) / 6; $i++) {
					$tem = new LogEntity();
					$tem->setSn($fileRes[1]); // sn
					$tem->setTime($temArr[$i * 6]);
					$tem->setEvent($temArr[$i * 6 + 1]);
					$tem->setWorkType($temArr[$i * 6 + 2]);
					$tem->setLevelNumber($temArr[$i * 6 + 3]);
					$tem->setLocation($temArr[$i * 6 + 4]);
					$tem->setMessage($temArr[$i * 6 + 5]);
					array_push($res, $tem);
				}
				$lmcl = $this->get("library_model_clean_log");
				$lmcl->addBatchEntity($res);
				return new Response($this->getRobotResultJson("0", "success", ""));
			}

			$fileName = $fileRes["fileName"];
			$url = str_replace($filePath, "", $fileName);
			$cleanUrl = ConfigHandler::getCommonConfig("cleanUrl");
			$url = $cleanUrl . $url;
			if (is_array($fileRes) && isset($fileRes['type']) && $fileRes['type'] == "txt") {
				$lmcmm = $this->get("library_model_clean_machinecleanrecord");
				$res = $lmcmm->getEntityBySnAndSort($fileRes['sn'], $fileRes['sort']);
				if (!$res) {
					$machineMapEntity = new MachineCleanRecordEntity();
					$machineMapEntity->setSn($fileRes['sn']);
					$machineMapEntity->setSort($fileRes['sort']);
					$machineMapEntity->setCleanArea($fileRes['cleanarea']);
					$machineMapEntity->setMopArea($fileRes['moparea']);
					$machineMapEntity->setStartTime($fileRes['starttime']);
					$machineMapEntity->setEndTime($fileRes['endtime']);
					$machineMapEntity->setCreateTime(new \DateTime(date('Y-m-d H:i:s', time())));
					/*$fileName = $fileRes["fileName"];
                    $url = $cleanUrl . str_replace($filePath, "", $fileName);*/
					$machineMapEntity->setUrl($url);
					$lmcmm = $this->get("library_model_clean_machinecleanrecord");
					$lmcmm->addEntity($machineMapEntity);
				}
				return new Response($this->getRobotResultJson("0", "success", ""));
			}

			$backupMapMd5 = $this->requestParameter("backupMapMd5");
			if ($backupMapMd5) {
				$fileRes = UploadFileHandler::requestUpload("backupMap", $filePath, true);

				//如果是地图，则插入数据库
				if (isset($fileRes["map"]) && $fileRes["map"]) {

					$sn = $this->requestParameter("sn");
					$createtime = $this->requestParameter("ts"); // 时间

					if (!$sn || !$backupMapMd5 || !$createtime) {
						return new Response($this->getRobotResultJson("100", "缺少参数", ""));
					}

					$machineMapEntity = new MachineMapEntity();
					$machineMapEntity->setSn($sn);
					$machineMapEntity->setBackupMd5($backupMapMd5);
					$createtime = date("Y-m-d H:i:s", $createtime);
					$machineMapEntity->setCreatetime($createtime);

					$fileName = $fileRes["fileName"];
					$url = $cleanUrl . str_replace($filePath, "", $fileName);
					$machineMapEntity->setUrl($url);

					$lmcmm = $this->get("library_model_clean_machinemap");
					$lmcmm->addEntity($machineMapEntity);
				}
			}

			return new Response($this->getRobotResultJson("0", "success", $url));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getRobotResultJson("200", "文件上传失败", ""));
		}
	}

	//上传扫地机日志
	public function uploadCleanLogAction() {
		try {

			if (!$this->validateCleanRobot()) {
				return new Response($this->getRobotResultJson("102", "你的cookies过期啦", ""));
			}

			$type = $this->requestParameter("type");
			$sn = $this->requestParameter("sn");
			$createtime = $this->requestParameter("ts"); // 时间
			if (!$type || !$sn || !$createtime) {
				return new Response($this->getRobotResultJson("100", "缺少参数", ""));
			}

			if ($type == 1) {
				$filePath = ConfigHandler::getCommonConfig("ordinaryLogPath");
				$cleanUrl = ConfigHandler::getCommonConfig("ordinaryLogUrl");
			} else {
				$filePath = ConfigHandler::getCommonConfig("collapseLogPath");
				$cleanUrl = ConfigHandler::getCommonConfig("collapseLogUrl");
			}

			$fileRes = UploadFileHandler::requestUploadTypeFile("logFile", $filePath, false);

			if (!is_array($fileRes) && strpos($fileRes, "@") > 0) {
				return new Response($this->getRobotResultJson("200", "fail", ""));
			}

			$fileName = $fileRes["fileName"];
			$url = str_replace($filePath, "", $fileName);
			$url = $cleanUrl . $url;

			$machineLogEntity = new MachineLogEntity();
			$machineLogEntity->setSn($sn);
			$machineLogEntity->setType($type);
			$createtime = new \DateTime(date("Y-m-d H:i:s", $createtime));
			$machineLogEntity->setUploadTime($createtime);
			$machineLogEntity->setUrl($url);

			$lmcml = $this->get("library_model_clean_machinelog");
			$lmcml->addEntity($machineLogEntity);

			return new Response($this->getRobotResultJson("0", "success", $url));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getRobotResultJson("200", "文件上传失败", ""));
		}
	}

	//更新扫地机扫地总数据
	public function updateCleanDataAction() {

		if (!$this->validateCleanRobot()) {
			return new Response($this->getRobotResultJson("102", "你的cookies过期啦", ""));
		}

		$sn = $this->requestParameter("sn");
		$data = $this->requestParameter("data");

		if (empty($sn) || empty($data)) {
			return new Response($this->getRobotResultJson("100", "缺少参数", ""));
		}

		$lmcm = $this->get("library_model_clean_machine");
		$machineEntity = $lmcm->isExistSn($sn);
		if (!$machineEntity) {
			return new Response($this->getRobotResultJson("103", "该sn无效", ""));
		}

		$data = json_decode($data, true);

		$lmcmd = $this->get("library_model_clean_machinedata");
		$machineDataInfo = $lmcmd->getMachineDataInfoBySn($sn);

		if (!$machineDataInfo) {
			$entity = new MachineDataEntity();
			$entity->setSn($sn);
			$entity->setTime($data["time"]);
			$entity->setMopArea($data["mopArea"]);
			$entity->setSweepArea($data["sweepArea"]);
			$entity->setCounts($data["counts"]);
			$lmcmd->addEntity($entity);
		} else {
			$machineDataInfo->setTime($data["time"]);
			$machineDataInfo->setMopArea($data["mopArea"]);
			$machineDataInfo->setSweepArea($data["sweepArea"]);
			$machineDataInfo->setCounts($data["counts"]);
			$lmcmd->editEntity($machineDataInfo);
		}

		return new Response($this->getRobotResultJson("0", "成功", ""));
	}

	//返回当前的毫秒时间戳4.
	private function msectime() {
		list($msec, $sec) = explode(' ', microtime());
		$msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

		return $msectime;
	}

	//在设备中添加该用户，重新获取
	private function addUserBySn($sn) {
		//获取文件的内容
		$filename = $this->getRobotFileName($sn);
		if ($filename) {
			$arr = $this->getRobot($filename);
			$lmcum = $this->get("library_model_clean_usermachine");
			$robotUserList = $lmcum->getUserRobotListBySN($sn);
			//修改文件的内容
			if ($arr) {
				$arr["userList"] = $robotUserList;
				$fp = fopen($filename, "w");
				fwrite($fp, json_encode($arr));
				fclose($fp);
			}
		}

		return true;
	}

	//在设备中删除该用户
	private function deleteUserBySn($sn) {
		$filename = $this->getRobotFileName($sn);
		if ($filename) {
			//获取文件的内容
			$arr = $this->getRobot($filename);
			$lmcum = $this->get("library_model_clean_usermachine");
			$robotUserList = $lmcum->getUserRobotListBySN($sn);
			//修改文件的内容
			if ($arr) {
				$arr["userList"] = $robotUserList;
				$fp = fopen($filename, "w");
				fwrite($fp, json_encode($arr));
				fclose($fp);
			}
		}

		return true;
	}

	/*
		     * 获取机器是否在线
	*/
	private function getRobotFileName($sn) {
		$subDir = substr($sn, 0, 2);
		$dir = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/robots/" . $subDir;

		$filename = $dir . "/" . $sn;
		if (!file_exists($filename)) {
			return false;
		}
		$content = $this->getRobot($filename);
		$fd = intval($content["fd"]);
		if (!$content || !$fd) {
			unlink($filename);
			return false;
		}

		$fdSubDir = substr(strval($fd), 0, 1);
		$fdFilename = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/fd/" . $fdSubDir . "/" . $fd;

		if (!file_exists($fdFilename)) {
			unlink($filename);
			return false;
		}

		return $filename;
	}

	//获取文件内容
	private function getRobot($filename) {
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			$result = json_decode($content, true);

			return $result;
		}
		return null;
	}

	private function saveMapData($data, $sn = "") {
		if (!$data || !$sn) {
			return false;
		}
		$dir = ConfigHandler::getCommonConfig("mapPath");

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$fileName = $dir . "/" . $sn;

		file_put_contents($fileName, $data);

		return true;
	}

	private function saveMapPathData($data, $sn = "", $ts = 0) {
		if (!$data || !$sn || !$ts) {
			return false;
		}
		$dir = ConfigHandler::getCommonConfig("routePath") . "/" . $sn;

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$fileName = $dir . "/" . $ts;

		file_put_contents($fileName, $data);
		return true;
	}

	//保存秘钥
	private function addMachineKey($sn, $key) {
		if (!$sn || !$key) {
			return false;
		}

		$dir = ConfigHandler::getCommonConfig("CONNECTOR_KEY");

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$filename = $dir . "/" . $sn;

		$fp = fopen($filename, "w");
		fwrite($fp, $key);
		fclose($fp);

		return true;
	}

	private function createAESKey($length = 16) {
		$str = '';
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol) - 1;
		for ($i = 0; $i < $length; $i++) {
			$str .= $strPol[rand(0, $max)];
		}
		return $str;
	}

}

?>
