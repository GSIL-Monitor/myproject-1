<?php

namespace Clean\APIBundle\Controller;

use Clean\APIBundle\Controller\BaseController;
use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\FirmwareEntity;
use Clean\LibraryBundle\Entity\FirmwareResult;
use Clean\LibraryBundle\Entity\MachineEntity;
use Clean\LibraryBundle\Entity\SystemMessageResult;
use Common\Utils\ConfigHandler;
use Common\Utils\Crypt\AESCryptHandler;
use Symfony\Component\HttpFoundation\Response;

class BasicInfoController extends BaseController {

	//扫地机静默更新
	public function silentUpdateFirmwareAction() {
		try
		{
			// if (!$this->validateCleanRobot()) {
			// 	return new Response($this->getRobotResultJson("102", "你的cookies过期啦", ""));
			// }

			$sn = $this->requestParameter("sn");
			$companyId = $this->requestParameter("companyId");

			if (!$sn || !$companyId) {
				return new Response($this->getRobotResultJson("100", "缺少参数", ""));
			}

			$lmcm = $this->get("library_model_clean_machine");
			$machineEntity = $lmcm->isExistSn($sn);

			$versionCode = $machineEntity->getVersion();

			if (!$versionCode) {
				return new Response($this->getRobotResultJson("105", "暂无版本信息", ""));
			}

			//获取可升级的版本号
			$temArr = explode('.', $versionCode);
			//最大版本号
			$newCode = intval($temArr[0] + 1) << 24 | 0 << 16 | 0;
			//当前版本号
			$versionCode = intval($temArr[0]) << 24 | intval($temArr[1]) << 16 | intval($temArr[2]);

			//获取当前最新的版本号
			$lmcf = $this->get("library_model_clean_firmware");
			$firmwareInfo = $lmcf->getSilentLatestFirmwareInfo($companyId, $newCode, $versionCode, $sn);

			if (!$firmwareInfo) {
				return new Response($this->getRobotResultJson("0", "no data", ""));
			} else {
				$url = ConfigHandler::getCommonConfig("firmwareUrl");
				$result = new FirmwareResult();
				$result->setFirmwareId($firmwareInfo->getFirmwareId());
				$result->setFirmwareName($firmwareInfo->getFirmwareName());
				$result->setCompanyId($firmwareInfo->getCompanyId());
				$result->setCheckCode($firmwareInfo->getCheckCode());
				$result->setVersionCode($firmwareInfo->getVersionCode());
				$result->setUrl($url . $firmwareInfo->getUrl());
				return new Response($this->getRobotResultJson("0", "success", $result));
			}

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getRobotResultJson("E01000", "服务器错误", ""));
		}
	}

	//获取APP广告页
	public function getAPPAdvertisementListAction() {
		try
		{
			$flag = $this->getParameter("flag");
			if (empty($flag)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$language = $this->getCurrentLanguage();
			$lma = $this->get("library_model_clean_advertisement");
			if ($language != "cn") {
				$language = "en";
			}

			$dataList = $lma->getDisplayAdvertisementByFlag($flag, $language);
			if (!$dataList && $language != "cn") {
				$dataList = $lma->getDisplayAdvertisementByFlag($flag, "cn");
			}
			return new Response($this->getAPIResultJson("N00000", "数据读取成功", $dataList));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//获取APP用户协议
	public function getUserProtocolAction() {

		$lmcbi = $this->get("library_model_clean_basicinfo");
		$language = $this->getCurrentLanguage();
		$basicInfo = $lmcbi->getBasicInfoByTypeAndCompanyId(3, 15, $language);

		$content = htmlspecialchars_decode($basicInfo->getContent());
		$content = str_replace("&nbsp;", " ", $content);

		$basicInfo->setContent($content);

		return $this->render("CleanAPIBundle:BasicInfo:userProtocol.html.twig", array("basicInfo" => $basicInfo));

	}

	//获取长连IP
	public function getIPAddressAction() {
		// $userIdAES = $this->getParameter("userId");
		// $userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
		// if (!$userId) {
		// 	$userId = intval($this->requestParameter("onlog"));
		// }
		// if (!is_int($userId) || $userId <= 0) {
		// 	return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
		// }
		// if (!$this->validateLoginUser($userId)) {
		// 	return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
		// }

		$data = array(
			"ip" => "39.107.127.173",
			"port" => 9501,
		);

		return new Response($this->getAPIResultJson("N00000", "获取成功", $data));

	}

	//获取错误码
	public function getErrorCodeAction() {
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
			$companyId = intval($this->getParameter("companyId"));
			if ($companyId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$intVersion = intval($this->getParameter("intVersion"));

			//1.查看是否有更高版本号
			$lmcecv = $this->get("library_model_clean_errorcodeversion");
			$errorCodeVersionInfo = $lmcecv->getHighVersion($intVersion);
			if ($errorCodeVersionInfo) {
				$lmcec = $this->get("library_model_clean_errorcode");
				$result = $lmcec->getErrorCodeInfoByCompanyId($companyId);
				$dataResult["intVersion"] = $errorCodeVersionInfo->getIntVersion();
				$dataResult["errorCodeInfo"] = $result;
				return new Response($this->getAPIResultJson("N00000", "数据获取成功", $dataResult));

			} else {
				return new Response($this->getAPIResultJson("N00000", "您已经是最高版本", ""));
			}

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//获取购买链接
	public function getGoodsUrlAction() {
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
		$companyId = intval($this->getParameter("companyId"));

		$lmcru = $this->get("library_model_clean_goodsurl");
		$result = $lmcru->getGoodsUrl($companyId);
		if ($result) {
			$url = $result->getUrl();
			return new Response($this->getAPIResultJson("N00000", "数据读取成功", $url));
		} else {
			return new Response($this->getAPIResultJson("N00000", "暂无数据", array()));
		}
	}

	public function getProductionInfoAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			if ($userIdAES) {
				$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
				if (!is_int($userId) || $userId <= 0) {
					return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
				}
				if (!$this->validateLoginUser($userId)) {
					return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
				}
				$companyId = intval($this->getParameter("companyId"));
				$language = $this->getCurrentLanguage();

				if ($companyId <= 0) {
					return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
				}
				$url = ConfigHandler::getCommonConfig("host") . ":" . ConfigHandler::getCommonConfig("port") . "/api/" . $language . "/getProductionInfo?companyId=" . $companyId;
				return new Response($this->getAPIResultJson("N00000", "数据获取成功", $url));
			} else {
				$companyId = intval($this->requestParameter("companyId"));
				if ($companyId <= 0) {
					return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
				}
				$lmcbi = $this->get("library_model_clean_basicinfo");
				$language = $this->getCurrentLanguage();
				$mainBasicInfo = $lmcbi->getBasicInfoByTypeAndCompanyId(1, $companyId, $language);
				if (!$mainBasicInfo) {
					$main = "";
				} else {
					$data = htmlspecialchars_decode($mainBasicInfo->getContent());
					$main = str_replace("&nbsp;", " ", $data);
				}
				$desBasicInfo = $lmcbi->getDesByTypeAndCompanyId(2, $companyId, $language);
				return $this->render("CleanAPIBundle:BasicInfo:productIntion.html.twig", array("basicInfo" => $main, "des" => $desBasicInfo));
			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	public function getProductionItemAction() {
		try
		{

			$basicInfoId = intval($this->requestParameter("basicInfoId"));
			if ($basicInfoId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcbi = $this->get("library_model_clean_basicinfo");
			$desBasicInfo = $lmcbi->getEntity($basicInfoId);
			$data = htmlspecialchars_decode($desBasicInfo->getContent());
			$desBasicInfo = str_replace("&nbsp;", " ", $data);
			return $this->render("CleanAPIBundle:BasicInfo:productIntion.html.twig", array("basicInfo" => $desBasicInfo, "des" => array()));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	public function getMessageListAction() {
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
			$companyId = intval($this->getParameter("companyId"));
			if ($companyId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcu = $this->get("library_model_clean_userinfo");
			$userDataEntity = $lmcu->getEntity($userId);
			$lastSystemMessageId = $userDataEntity->getLastSystemMessageId();

			$dataResult = array();
			$lmcsm = $this->get("library_model_clean_systemmessage");
			$result = $lmcsm->getUnReadMessageList($lastSystemMessageId, $companyId, $userId);
			$systemMessageId = 0;
			if (!empty($result)) {
				$systemMessageId = $result[0]->getSystemMessageId();
				$isRead = 0;
				for ($i = 0; $i < count($result); $i++) {
					$tempResult = new SystemMessageResult();
					$tempResult->setSystemMessageId($result[$i]->getSystemMessageId());

					$content = $result[$i]->getMessageContent();
					if (strpos($content, "您已经解绑扫地机") !== false) {
						$temArr = explode(",", $content);
						$content = $this->tranlate($temArr[0]) . $temArr[1];
					} elseif (strpos($content, "您已经绑定扫地机") !== false) {
						$temArr = explode(",", $content);
						$content = $this->tranlate($temArr[0]) . $temArr[1];
					}
					$tempResult->setMessageContent($content);
					$tempResult->setCreateTime($result[$i]->getCreateTime());
					$tempResult->setIsRead($isRead);
					array_push($dataResult, $tempResult);
				}

			}
			//取已读的
			$result = $lmcsm->getReadMessageList($lastSystemMessageId, $companyId, $userId);
			if (!empty($result)) {
				$isRead = 1;
				for ($i = 0; $i < count($result); $i++) {
					$tempResult = new SystemMessageResult();
					$tempResult->setSystemMessageId($result[$i]->getSystemMessageId());

					$content = $result[$i]->getMessageContent();
					if (strpos($content, "您已经解绑扫地机") !== false) {
						$temArr = explode(",", $content);
						$content = $this->tranlate($temArr[0]) . $temArr[1];
					} elseif (strpos($content, "您已经绑定扫地机") !== false) {
						$temArr = explode(",", $content);
						$content = $this->tranlate($temArr[0]) . $temArr[1];
					}

					$tempResult->setMessageContent($content);
					$tempResult->setCreateTime($result[$i]->getCreateTime());
					$tempResult->setIsRead($isRead);
					array_push($dataResult, $tempResult);
				}
			}

			//更新最后阅读ID
			if ($systemMessageId > 0) {
				$userDataEntity->setLastSystemMessageId($systemMessageId);
				$lmcu->editEntity($userDataEntity);
			}

			return new Response($this->getAPIResultJson("N00000", "数据获取成功", $dataResult));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//固件更新
	public function updateFirmwareAction() {
		try
		{
			$token = $this->requestParameter("token");
			$tem = md5(md5(CommonDefine::AES_KEY));
			if (!$token || $token != $tem) {
				return new Response($this->getAPIResultJson("E03000", "fail", ""));
			}
			$companyId = intval($this->requestParameter("companyId"));
			$versionCode = $this->requestParameter("versionCode");
			if ($companyId <= 0 || !$versionCode) {
				return new Response($this->getAPIResultJson("E02000", "fali", ""));
			}
			//获取当前最新的版本号
			$lmcf = $this->get("library_model_clean_firmware");

			//获取可升级的版本号
			$temArr = explode('.', $versionCode);
			$newCode = intval($temArr[0] + 1) << 24 | 0 << 16 | 0;

			$versionCode = intval($temArr[0]) << 24 | intval($temArr[1]) << 16 | intval($temArr[2]);

			$firmwareInfo = $lmcf->getLatestFirmwareInfo($companyId, $newCode, $versionCode);
			$url = ConfigHandler::getCommonConfig("firmwareUrl");
			if (!empty($firmwareInfo)) {
				$firmwareInfo->setUrl($url . $firmwareInfo->getUrl());
				return new Response($this->getAPIResultJson("N00000", "success", $firmwareInfo));
			}

			return new Response($this->getAPIResultJson("N00001", "your firmware is lastest", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//更新APP
	public function updateAppVersionAction() {
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
			$appType = intval($this->getParameter("appType"));
			$companyId = intval($this->getParameter("companyId"));
			$versionCode = $this->getParameter("versionCode");
			if ($appType <= 0 || $companyId <= 0 || !$versionCode) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			if (intval($versionCode) < 1) {
				if ($versionCode == "V1.4.0") {
					$versionCode = 192;
				} else {
					$versionCode = 190;
				}
			}

			$lmcav = $this->get("library_model_clean_appversion");
			$result = $lmcav->getLatestAppVersionInfo($appType, $companyId);
			if ($result) {
				$latestVersion = $result->getVersionCode();
				if (!version_compare($versionCode, $latestVersion, '>=')) {
					// $url = ConfigHandler::getCommonConfig("appFileUrl");
					// $result->setUrl($url . $result->getUrl());
					return new Response($this->getAPIResultJson("N00000", "数据获取成功", $result));
				}
			}
			return new Response($this->getAPIResultJson("N00000", "您的版本已最新", ""));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function getMessageContentAction() {
		try
		{
			$id = intval($this->requestParameter("id"));
			if ($id <= 0) {
				return new Response($this->getAPIResultJson("E03000", "链接错误", ""));
			}
			$lmcmc = $this->get("library_model_clean_messagecontent");
			$messageContentEntity = $lmcmc->getEntity($id);
			$data = $messageContentEntity->getContent();
			if ($data) {
				$data = htmlspecialchars_decode($data);
				$data = str_replace("&nbsp;", " ", $data);
			}
			return $this->render("CleanAPIBundle:BasicInfo:messageContent.html.twig", array("basicInfo" => $data));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//上传SN
	public function uploadMachineAction() {
		try
		{
			$token = $this->requestParameter("token");
			$tem = md5(md5(CommonDefine::AES_KEY));
			if (!$token || $token != $tem) {
				return new Response($this->getAPIResultJson("E03000", "fail", ""));
			}
			$companyId = intval($this->getParameter("companyId"));
			$machineType = intval($this->getParameter("machineType"));
			$sn = $this->getParameter("sn");
			if ($companyId != -1 && $companyId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "missing parameter", ""));
			}
			if (!$machineType || !$sn) {
				return new Response($this->getAPIResultJson("E02000", "missing parameter", ""));
			}
			$lmcm = $this->get("library_model_clean_machine");

			$machineInfo = $lmcm->isExistSn($sn);
			if ($machineInfo) {
				return new Response($this->getAPIResultJson("E04000", "data having exist", ""));
			}
			$entity = new MachineEntity();
			$entity->setSn($sn);
			$entity->setCompanyId($companyId);
			$entity->setMachineType($machineType);
			$machineId = $lmcm->addEntity($entity);
			return new Response($this->getAPIResultJson("N00000", " add success", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//APP申请固件更新
	public function updateFirmwareAPPAction() {
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

			$companyId = intval($this->getParameter("companyId"));
			// if( $companyId <= 0 )
			// {
			//     return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			// }

			//获取当前版本号
			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfo = $lmcu->getEntity($userId);
			$nowSn = $userInfo->getNowSn();

			$res = new FirmwareEntity();
			if (!$nowSn) {
				return new Response($this->getAPIResultJson("N00000", "您没有绑定扫地机", ""));
			}

			$lmcm = $this->get("library_model_clean_machine");
			$machineEntity = $lmcm->isExistSn($nowSn);

			$versionCode = $machineEntity->getVersion();

			if (!$versionCode) {
				return new Response($this->getAPIResultJson("N00000", "暂无版本信息", ""));
			}

			//获取可升级的版本号
			$temArr = explode('.', $versionCode);
			//最大版本号
			$newCode = intval($temArr[0] + 1) << 24 | 0 << 16 | 0;
			//当前版本号
			$versionCode = intval($temArr[0]) << 24 | intval($temArr[1]) << 16 | intval($temArr[2]);

			//获取当前最新的版本号
			$lmcf = $this->get("library_model_clean_firmware");
			$firmwareInfo = $lmcf->getLatestFirmwareInfo($companyId, $newCode, $versionCode, $nowSn);

			if ($firmwareInfo) {
				$status = 1;
			} else {
				//如果已经是最新则获取当前版本
				$status = 0;
				$firmwareInfo = $lmcf->getFirmwareInfoByVersion($companyId, $versionCode);
			}

			if (!$firmwareInfo) {
				$result = new FirmwareResult();
				$result->setIntVersionCode($versionCode);
				$result->setStatus($status);
				$result->nowVersionCode = $machineEntity->getVersion();
				return new Response($this->getAPIResultJson("N00000", "暂无版本信息", $result));
			}

			$firmwareInfo->setStatus($status);

			$url = ConfigHandler::getCommonConfig("firmwareUrl");
			$language = $this->getCurrentLanguage();
			if (!empty($firmwareInfo)) {
				$result = new FirmwareResult();
				$result->setFirmwareId($firmwareInfo->getFirmwareId());
				$result->setFirmwareName($firmwareInfo->getFirmwareName());
				$result->setDescription($firmwareInfo->getDescription());
				$result->setCompanyId($firmwareInfo->getCompanyId());
				$result->setCheckCode($firmwareInfo->getCheckCode());
				$result->setCreateTime($firmwareInfo->getCreateTime());
				$result->setIsAutoUpdate($firmwareInfo->getIsAutoUpdate());
				$result->setStatus($firmwareInfo->getStatus());
				$result->nowVersionCode = $machineEntity->getVersion();
				$result->setIntVersionCode($versionCode);

				if ($language != "cn") {
					$result->setDescription($firmwareInfo->getEnDescription());
				}

				//如果有显示的版本号
				if ($firmwareInfo->getDisplayVersionCode()) {
					$result->setVersionCode($firmwareInfo->getDisplayVersionCode());
				} else {
					$result->setVersionCode($firmwareInfo->getVersionCode());
				}

				$result->setUrl($url . $firmwareInfo->getUrl());
				return new Response($this->getAPIResultJson("N00000", "数据获取成功", $result));
			}

			return new Response($this->getAPIResultJson("N00000", "您的版本已最新", ""));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

}
?>