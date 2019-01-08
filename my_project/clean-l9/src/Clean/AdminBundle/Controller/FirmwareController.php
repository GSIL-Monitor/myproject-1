<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\FirmwareEntity;
use Clean\LibraryBundle\Entity\FirmwareOperationRecordEntity;
use Clean\LibraryBundle\Entity\FirmwareResult;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Symfony\Component\HttpFoundation\Response;

class FirmwareController extends BaseController {

	public function firmwarePageListAction() {
		return $this->render("CleanAdminBundle:Firmware:firmwarePageList.html.twig", array());
	}

	public function getFirmwarePageListAction() {
		try
		{

			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$lmcf = $this->get("library_model_clean_firmware");
			$pageIndex = intval($this->requestParameter("pageIndex"));
			if (empty($pageIndex)) {
				$pageIndex = 1;
			}

			$pageSize = intval($this->requestParameter("pageSize"));
			if (empty($pageSize)) {
				$pageSize = 30;
			}

			$versionCode = $this->requestParameter("versionCode");
			$intVersionCode = 0;
			if ($versionCode) {
				$temArr = explode(".", $versionCode);
				$intVersionCode = intval($temArr[0]) << 24 | intval($temArr[1]) << 16 | intval($temArr[2]);
			}
			//1.等于 2.大于
			$searchType = intval($this->requestParameter("searchType"));

			$result = $lmcf->getPageFirmware($pageIndex, $pageSize, $intVersionCode, $searchType);
			if ($result) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
			} else {
				return new Response($this->getAPIResultJson("E02000", "数据读取失败", ""));
			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addFirmwareAction() {
		try
		{
			$lmcc = $this->get("library_model_clean_company");
			//$companyInfo = $lmcc->getEntityList();
			$companyId = $this->CompanyId;
			if ($companyId == -1) {
				$isAdmin = true;
				$companyInfo = $lmcc->getEntityList();
			} else {
				$isAdmin = false;
				$result = $lmcc->getEntity($companyId);
				$companyInfo = array();
				$companyInfo[] = $result;
			}

			$lmcwg = $this->get("library_model_clean_whitegroup");
			$whiteGroupInfo = $lmcwg->getEntityList();
			return $this->render("CleanAdminBundle:Firmware:addFirmare.html.twig", array(
				"companyInfo" => $companyInfo,
				"whiteGroupInfo" => $whiteGroupInfo,
				"isAdmin" => $isAdmin,
			));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addFirmwareSubmitAction() {
		try
		{
			// if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }
			$companyId = intval($this->requestParameter("companyId"));

			// if($companyId <= 0 && $companyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }

			if ($companyId <= 0) {
				$companyId = $this->CompanyId;
			}

			$versionCode = $this->requestParameter("versionCode");
			$firmwareName = $this->requestParameter("firmwareName");
			$url = $this->requestParameter("url");
			$description = $this->requestParameter("description");
			$enDescription = $this->requestParameter("enDescription");
			$checkCode = $this->requestParameter("checkCode");

			if (!$versionCode || !$firmwareName || !$url || !$checkCode) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcf = $this->get("library_model_clean_firmware");
			$firmware = $lmcf->getFirmwareByVersionCodeAndCompanyId($versionCode, $companyId);
			if ($firmware) {
				return new Response($this->getAPIResultJson("E02000", "该版本号固件已经存在，请勿重复添加", ""));
			}
			$temArr = explode(".", $versionCode);
			$intVersionCode = intval($temArr[0]) << 24 | intval($temArr[1]) << 16 | intval($temArr[2]);
			$entity = new FirmwareEntity();
			$entity->setCompanyId($companyId);
			$entity->setVersionCode($versionCode);
			$entity->setFirmwareName($firmwareName);
			$entity->setFirmwareName($firmwareName);
			$entity->setUrl($url);
			$entity->setCheckCode($checkCode);
			$entity->setIntVersionCode($intVersionCode);

			$isAutoUpdate = intval($this->requestParameter("autoUpdate"));
			$entity->setIsAutoUpdate($isAutoUpdate);
			if (!empty($description)) {
				$entity->setDescription($description);
			}
			if (!empty($enDescription)) {
				$entity->setEnDescription($enDescription);
			}

			$entity->setDisplayVersionCode($this->requestParameter("displayVersionCode"));

			//上传白名单分组
			$whiteGroupIds = $this->requestParameter("whiteGroupIds");
			if (strlen($whiteGroupIds) == 0) {
				return new Response($this->getAPIResultJson("E02000", "请勾选白名单", ""));
			}

			if ($whiteGroupIds == 0 || $isAutoUpdate == 1) {
				//记录操作
				$name = $this->requestParameter("name");
				if (!$name) {
					return new Response($this->getAPIResultJson("E02000", "请填写您的名字", ""));
				}
				$adminUserId = $this->LoginUserId;
				$lmcfor = $this->get("library_model_clean_firmwareoperationrecord");
				$firmwareOperationRecordEntity = new FirmwareOperationRecordEntity();
				$firmwareOperationRecordEntity->setAdminUserId($adminUserId);
				$firmwareOperationRecordEntity->setIsAutoUpdate($isAutoUpdate);

				if ($whiteGroupIds == 0) {
					$isAllSn = 1;
				} else {
					$isAllSn = 0;
				}
				$firmwareOperationRecordEntity->setIsAllSn($isAllSn);
				$firmwareOperationRecordEntity->setName($name);
				$lmcfor->addEntity($firmwareOperationRecordEntity);
			}

			if (!$whiteGroupIds) {
				$sns = 0;
				$whiteGroupIds = 0;
			} else {
				$listArr = explode(",", $whiteGroupIds);
				$lmcwgs = $this->get("library_model_clean_whitegroupsn");
				$sns = '';
				for ($i = 0; $i < count($listArr); $i++) {
					if ($listArr[$i] > 0) {
						$groupSnInfo = $lmcwgs->getSnByWhiteGroupId($listArr[$i]);
						if ($groupSnInfo) {
							for ($j = 0; $j < count($groupSnInfo); $j++) {
								$sns .= $groupSnInfo[$j]["sn"] . ",";
							}
						}
					}
				}

				if (!$sns || strpos("0000000000000000", $sns) != false) {
					return new Response($this->getAPIResultJson("E02000", "白名单组不符合规范，不能为空或者包含16个0", ""));
				}
			}
			$entity->setSns($sns);
			$entity->setWhiteGroupIds($whiteGroupIds);

			$firmwareId = $lmcf->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editFirmwareAction() {
		try
		{
			// if($this->CompanyId != -1)
			// {
			//   return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }

			$firmwareId = intval($this->requestParameter("firmwareId"));
			if ($firmwareId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}

			$companyId = intval($this->requestParameter("companyId"));
			if (!$companyId) {
				$companyId = $this->CompanyId;
			}

			$lmcc = $this->get("library_model_clean_company");
			if ($companyId == -1) {
				$companyInfo = $lmcc->getEntityList();
			} else {
				$companyInfo[] = $lmcc->getEntity($companyId);
			}

			$lmcf = $this->get("library_model_clean_firmware");
			$firmwareEntity = $lmcf->getEntity($firmwareId);
			$firmwareInfo = new FirmwareResult();
			if ($firmwareEntity) {
				$companyId = $firmwareEntity->getCompanyId();
				if ($companyId == -1) {
					$companyName = '通用';
				} else {
					$companyNameInfo = $lmcc->getEntity($companyId);
					$companyName = $companyNameInfo->getCompanyName();
				}
				$url = ConfigHandler::getCommonConfig("firmwareUrl");
				$firmwareInfo->setFirmwareId($firmwareEntity->getFirmwareId());
				$firmwareInfo->setVersionCode($firmwareEntity->getVersionCode());
				$firmwareInfo->setFirmwareName($firmwareEntity->getFirmwareName());
				$firmwareInfo->setUrl($firmwareEntity->getUrl());
				$firmwareInfo->setDescription($firmwareEntity->getDescription());
				$firmwareInfo->setEnDescription($firmwareEntity->getEnDescription());
				$firmwareInfo->setCompanyId($firmwareEntity->getCompanyId());
				$firmwareInfo->setCheckCode($firmwareEntity->getCheckCode());
				$firmwareInfo->setCreateTime($firmwareEntity->getCreateTime());
				$firmwareInfo->setCompanyName($companyName);
				$firmwareInfo->autoUpdate = $firmwareEntity->getIsAutoUpdate();

				$firmwareInfo->setDisplayVersionCode($firmwareEntity->getDisplayVersionCode());
				$firmwareInfo->setSns($firmwareEntity->getSns());
				$firmwareInfo->setWhiteGroupIds($firmwareEntity->getWhiteGroupIds());

			}

			$lmcwg = $this->get("library_model_clean_whitegroup");
			$whiteGroupInfo = $lmcwg->getEntityList();

			return $this->render("CleanAdminBundle:Firmware:editFirmare.html.twig", array(
				"companyInfo" => $companyInfo,
				"whiteGroupInfo" => $whiteGroupInfo,
				"firmwareInfo" => $firmwareInfo,
			));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editFirmwareSubmitAction() {
		try
		{
			// if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }
			$firmwareId = intval($this->requestParameter("firmwareId"));
			if ($firmwareId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$companyId = intval($this->requestParameter("companyId"));
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$versionCode = $this->requestParameter("versionCode");
			$firmwareName = $this->requestParameter("firmwareName");
			$url = $this->requestParameter("url");
			$description = $this->requestParameter("description");
			$enDescription = $this->requestParameter("enDescription");
			$checkCode = $this->requestParameter("checkCode");

			if (!$versionCode || !$firmwareName || !$url || !$checkCode) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$temArr = explode(".", $versionCode);
			$intVersionCode = intval($temArr[0]) << 24 | intval($temArr[1]) << 16 | intval($temArr[2]);

			$lmcf = $this->get("library_model_clean_firmware");
			$firmwareEntity = $lmcf->getEntity($firmwareId);

			$firmwareEntity->setCompanyId($companyId);
			$firmwareEntity->setVersionCode($versionCode);
			$firmwareEntity->setFirmwareName($firmwareName);
			$firmwareEntity->setUrl($url);
			$firmwareEntity->setCheckCode($checkCode);

			$isAutoUpdate = intval($this->requestParameter("autoUpdate"));
			$firmwareEntity->setIsAutoUpdate($isAutoUpdate);
			if (!empty($description)) {
				$firmwareEntity->setDescription($description);
			}
			if (!empty($enDescription)) {
				$firmwareEntity->setEnDescription($enDescription);
			}
			$firmwareEntity->setIntVersionCode($intVersionCode);

			$firmwareEntity->setDisplayVersionCode($this->requestParameter("displayVersionCode"));
			//白名单分组
			$whiteGroupIds = $this->requestParameter("whiteGroupIds");
			if (strlen($whiteGroupIds) == 0) {
				return new Response($this->getAPIResultJson("E02000", "请勾选白名单", ""));
			}

			if ($whiteGroupIds == 0 || $isAutoUpdate == 1) {
				//记录操作
				$name = $this->requestParameter("name");
				if (!$name) {
					return new Response($this->getAPIResultJson("E02000", "请填写您的名字", ""));
				}
				$adminUserId = $this->LoginUserId;
				$lmcfor = $this->get("library_model_clean_firmwareoperationrecord");
				$firmwareOperationRecordEntity = new FirmwareOperationRecordEntity();
				$firmwareOperationRecordEntity->setAdminUserId($adminUserId);
				$firmwareOperationRecordEntity->setIsAutoUpdate($isAutoUpdate);

				if ($whiteGroupIds == 0) {
					$isAllSn = 1;
				} else {
					$isAllSn = 0;
				}
				$firmwareOperationRecordEntity->setIsAllSn($isAllSn);
				$firmwareOperationRecordEntity->setName($name);
				$lmcfor->addEntity($firmwareOperationRecordEntity);
			}

			if (!$whiteGroupIds) {
				$sns = 0;
				$whiteGroupIds = 0;
			} else {
				$listArr = explode(",", $whiteGroupIds);
				$lmcwgs = $this->get("library_model_clean_whitegroupsn");
				$sns = '';
				for ($i = 0; $i < count($listArr); $i++) {
					if ($listArr[$i] > 0) {
						$groupSnInfo = $lmcwgs->getSnByWhiteGroupId($listArr[$i]);
						if ($groupSnInfo) {
							for ($j = 0; $j < count($groupSnInfo); $j++) {
								$sns .= $groupSnInfo[$j]["sn"] . ",";
							}
						}
					}
				}

				if (!$sns || strpos("0000000000000000", $sns) != false) {
					return new Response($this->getAPIResultJson("E02000", "白名单组不符合规范，不能为空或者包含16个0", ""));
				}
			}
			$firmwareEntity->setSns($sns);
			$firmwareEntity->setWhiteGroupIds($whiteGroupIds);
			$firmware = $lmcf->editEntity($firmwareEntity);
			return new Response($this->getAPIResultJson("N00000", "修改成功", ""));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function deleteFirmwareListAction() {
		try
		{
			//if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }

			$firmwareIdList = $this->requestParameter("firmwareIdList");
			if (!$firmwareIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $firmwareIdList);
			$lmcf = $this->get("library_model_clean_firmware");
			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$lmcf->deleteEntity($listArr[$i]);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function uploadFirmwareFileAction() {
		$filePath = ConfigHandler::getCommonConfig("firmwarePath");
		$uploadResult = UploadFileHandler::requestUploadTypeFile("file", $filePath, false);
		if (!is_array($uploadResult)) {
			return new Response($this->getAPIResultJson("E01000", strval($uploadResult), ""));
		}
		$fileName = $uploadResult["fileName"];
		$uploadResult["fileName"] = str_replace($filePath, "", $fileName);
		return new Response($this->getAPIResultJson("N00000", "上传成功", $uploadResult));
	}

	public function checkFirmwarePasswordAction() {

		$firmwarePassword = $this->requestParameter("firmwarePassword");
		if (!$firmwarePassword || strlen($firmwarePassword) != 32) {
			return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
		}
		$password = md5("check-firmware-l9-123");
		if ($firmwarePassword != $password) {
			return new Response($this->getAPIResultJson("E02000", "认证失败", ""));
		}
		return new Response($this->getAPIResultJson("N00000", "认证成功", ""));
	}

}
?>