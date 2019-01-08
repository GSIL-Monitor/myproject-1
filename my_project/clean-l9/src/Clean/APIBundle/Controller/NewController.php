<?php

namespace Clean\APIBundle\Controller;

use Clean\APIBundle\Controller\BaseController;
use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\MachineKeyEntity;
use Common\Utils\AlexaHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\Crypt\AESCryptHandler;
use Common\Utils\Crypt\RSAHandler;
use Common\Utils\LogHandler;
use Symfony\Component\HttpFoundation\Response;

class NewController extends BaseController {
	//APP获取地图数据
	public function getMapDataAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$sn = $this->getParameter("sn");

			if (!$sn) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			//获取地图路径
			$path = ConfigHandler::getCommonConfig("mapPath");
			$filename = $path . "/" . $sn;
			$mapData = file_get_contents($filename);
			$mapArr = json_decode($mapData, true);
			if ($mapArr["data"]) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $mapArr["data"]));
			}

			return new Response($this->getAPIResultJson("N00000", "暂无数据", ''));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//检测秘钥是否可以
	public function checkMachineAction() {
		$sn = $this->requestParameter("sn");
		$signature = $this->requestParameter("signature"); //验证签名

		if (!$sn || !$signature) {
			return new Response($this->getResultJson("100", "数据填写不完整", ""));
		}

		$lmcm = $this->get("library_model_clean_machine");
		$machineEntity = $lmcm->isExistSn($sn);
		if (!$machineEntity) {
			return new Response($this->getResultJson("250", "该sn无效", ""));
		}

		$lmcmk = $this->get("library_model_clean_machinekey");
		$machineKeyInfo = $lmcmk->getMachineKeyInfoBySn($sn);
		if ($machineKeyInfo) {
			$key = $machineKeyInfo->getPrivateKey();
		} else {
			return new Response($this->getResultJson("251", "签名无效", ""));
		}
		$signature = str_replace(" ", "+", $signature);
		$signature = str_replace("&nbsp;", "+", $signature);
		//var_dump($signature);
		$str = RSAHandler::rasDecrypt($signature, $key);
		//var_dump($str);

		if (strpos($str, $sn) === false) {
			return new Response($this->getResultJson("251", "签名无效", ""));
		}

		return new Response($this->getResultJson("0", "成功", ""));

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
		// $fp = fopen($fileName, "a");
		// fwrite($fp, $msg);
		//fclose($fp);

		return true;
	}

	private function saveMapPathData($data, $sn = "", $taskid = 0) {
		if (!$data || !$sn || !$taskid) {
			return false;
		}
		$dir = ConfigHandler::getCommonConfig("routePath") . "/" . $sn;

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$fileName = $dir . "/" . $taskid;

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

	//批量生成多个Sn----暂时先不用
	public function createMachineAction() {

		return new Response($this->getAPIResultJson("E03000", "关闭", ""));

		if (!$this->validateLoginUser(1)) {
			return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
		}

		if ($this->LoginUserId != 4) {
			return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
		}

		$count = intval($this->getParameter("count")) ? intval($this->getParameter("count")) : 10;
		$start = intval($this->getParameter("start")) ? intval($this->getParameter("start")) : 1;
		$sn = $this->getParameter("sn");
		$result = '';
		$lmcmk = $this->get("library_model_clean_machinekey");
		if ($sn && strlen($sn) == 16) {
			$machineKeyInfo = $lmcmk->getMachineKeyInfoBySn($sn);
			if ($machineKeyInfo) {
				// $key = RSAHandler::createKey();
				// $machineKeyInfo->setPrivateKey($key["private_key"]);
				// $machineKeyInfo->setPublicKey($key["public_key"]);
				// $machineKeyInfo->setSn($sn);
				// $lmcmk->editEntity($machineKeyInfo);

				$key = array("public_key" => $machineKeyInfo->getPublicKey());
			} else {
				$entity = new MachineKeyEntity();
				$key = RSAHandler::createKey();
				$entity->setPrivateKey($key["private_key"]);
				$entity->setPublicKey($key["public_key"]);
				$entity->setSn($sn);
				$lmcmk->addEntity($entity);
			}

			$result = $key["public_key"];
		} else {
			$entityArr = array();
			for ($i = 0; $i < $count; $i++) {

				$entity = new MachineKeyEntity();

				$key = RSAHandler::createKey();

				$entity->setPrivateKey($key["private_key"]);
				$entity->setPublicKey($key["public_key"]);
				$num = $start + $i;
				$sn = $this->createSn($num);

				$entity->setSn($sn);
				array_push($entityArr, $entity);
			}

			$lmcmk->addBatchEntity($entityArr);

		}

		var_dump($result);
		return new Response($this->getAPIResultJson("N00000", "读取成功", ''));
	}

	private function createSn($key) {
		$preSn = "1851A021ABCD";
		$temStr = '';
		$max = 16 - strlen($preSn) - strlen($key);
		for ($i = 0; $i < $max; $i++) {
			$temStr .= "0";
		}

		return $preSn . $temStr . $key;
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

	public function testDataAction() {

		$sn = "12607030CFE70013";
		$mapPushData = array(
			"infoType" => 21024,
			"connectionType" => 1,
			"deviceType" => 4,
		);

		$mapPushData["data"]["sn"] = $sn;
		$mapPushData["data"]["cmd"] = "reboot";
		$mapPushData["data"]["value"] = 2000;
		$taskid = time();
		$mapPushData["extend"] = array("taskid" => $taskid);
		$usid = 0;
		if ($usid) {
			$mapPushData["extend"]["userId"] = $usid;
		}

		$mapPushData = json_encode($mapPushData, JSON_UNESCAPED_SLASHES) . "#\t#";
		$mapPushData = str_replace(" ", "+", $mapPushData);
		$res = AlexaHandler::swooleClient($mapPushData, 9501);

		LogHandler::writeLog(json_encode($res) . "\r\n" . $taskid . "\r\n", "https-21024");
		return new Response($this->getResultJson("0", "success", ""));
	}

}

?>
