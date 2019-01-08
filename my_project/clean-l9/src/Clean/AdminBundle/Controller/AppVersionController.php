<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\AppVersionEntity;
use Clean\LibraryBundle\Entity\AppVersionResult;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Symfony\Component\HttpFoundation\Response;

class AppVersionController extends BaseController {

	public function appVersionPageListAction() {
		return $this->render("CleanAdminBundle:AppVersion:appVersionPageList.html.twig", array());
	}

	public function getAppVersionPageListAction() {
		try
		{
			$lmcav = $this->get("library_model_clean_appversion");
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$pageIndex = intval($this->requestParameter("pageIndex"));
			if (empty($pageIndex)) {
				$pageIndex = 1;
			}

			$pageSize = intval($this->requestParameter("pageSize"));
			if (empty($pageSize)) {
				$pageSize = 30;
			}
			$result = $lmcav->getPageAppVersion($pageIndex, $pageSize);
			if ($result) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
			} else {
				return new Response($this->getAPIResultJson("E02000", "数据读取失败", ""));
			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addAppVersionAction() {
		try
		{
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityList();
			return $this->render("CleanAdminBundle:AppVersion:addAppVersion.html.twig", array(
				"companyInfo" => $companyInfo,
			));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addAppVersionSubmitAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$companyId = intval($this->requestParameter("companyId"));
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$versionCode = $this->requestParameter("versionCode");
			$appName = $this->requestParameter("appName");
			$url = $this->requestParameter("url");
			$description = $this->requestParameter("description");
			$appType = $this->requestParameter("appType");

			if (!$versionCode || !$url || !$appType) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcav = $this->get("library_model_clean_appversion");
			// $appVersion = $lmcav->getAppVersionByVersionCodeAndCompanyId($versionCode,$companyId);
			// if($appVersion)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "该版本号固件已经存在，请勿重复添加", ""));
			// }
			$entity = new AppVersionEntity();
			$entity->setCompanyId($companyId);
			$entity->setVersionCode($versionCode);
			$entity->setAppType($appType);
			$entity->setUrl($url);
			if (!empty($description)) {
				$entity->setDescription($description);
			}
			if (!empty($appName)) {
				$entity->setAppName($appName);
			}
			$appVersionId = $lmcav->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editAppVersionAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$appVersionId = intval($this->requestParameter("appVersionId"));
			if ($appVersionId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityList();
			$lmcav = $this->get("library_model_clean_appversion");
			$appVersionEntity = $lmcav->getEntity($appVersionId);
			$appVersionInfo = new AppVersionResult();
			if ($appVersionEntity) {
				$companyId = $appVersionEntity->getCompanyId();
				if ($companyId == -1) {
					$companyName = '通用';
				} else {
					$companyNameInfo = $lmcc->getEntity($companyId);
					$companyName = $companyNameInfo->getCompanyName();
				}
				$appVersionInfo->setAppVersionId($appVersionEntity->getAppVersionId());
				$appVersionInfo->setVersionCode($appVersionEntity->getVersionCode());
				$appVersionInfo->setAppName($appVersionEntity->getAppName());
				$appVersionInfo->setUrl($appVersionEntity->getUrl());
				$appVersionInfo->setDescription($appVersionEntity->getDescription());
				$appVersionInfo->setCompanyId($appVersionEntity->getCompanyId());
				$appVersionInfo->setAppType($appVersionEntity->getAppType());
				$appVersionInfo->setCreateTime($appVersionEntity->getCreateTime());
				$appVersionInfo->setCompanyName($companyName);
			}
			return $this->render("CleanAdminBundle:AppVersion:editAppVersion.html.twig", array("companyInfo" => $companyInfo, "appVersionInfo" => $appVersionInfo));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editAppVersionSubmitAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$appVersionId = intval($this->requestParameter("appVersionId"));
			if ($appVersionId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$companyId = intval($this->requestParameter("companyId"));
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$versionCode = $this->requestParameter("versionCode");
			$appName = $this->requestParameter("appName");
			$url = $this->requestParameter("url");
			$description = $this->requestParameter("description");
			$appType = $this->requestParameter("appType");

			if (!$versionCode || !$url || !$appType) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcav = $this->get("library_model_clean_appversion");
			$appVersionEntity = $lmcav->getEntity($appVersionId);

			$appVersionEntity->setCompanyId($companyId);
			$appVersionEntity->setVersionCode($versionCode);
			$appVersionEntity->setAppType($appType);
			$appVersionEntity->setUrl($url);
			if (!empty($description)) {
				$appVersionEntity->setDescription($description);
			}
			if (!empty($appName)) {
				$appVersionEntity->setAppName($appName);
			}
			$appVersion = $lmcav->editEntity($appVersionEntity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function deleteAppVersionListAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$appVersionIdList = $this->requestParameter("appVersionIdList");
			if (!$appVersionIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $appVersionIdList);
			$lmcav = $this->get("library_model_clean_appversion");
			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$lmcav->deleteEntity($listArr[$i]);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function uploadAppVersionFileAction() {

		$filePath = ConfigHandler::getCommonConfig("appFilePath");
		$uploadResult = UploadFileHandler::requestUploadTypeFile("file", $filePath, false);
		if (!is_array($uploadResult)) {
			return new Response($this->getAPIResultJson("E01000", strval($uploadResult), ""));
		}
		$fileName = $uploadResult["fileName"];
		$uploadResult["fileName"] = str_replace($filePath, "", $fileName);
		return new Response($this->getAPIResultJson("N00000", "上传成功", $uploadResult));
	}

}
?>