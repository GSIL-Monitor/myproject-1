<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\ErrorCodeEntity;
use Clean\LibraryBundle\Entity\ErrorCodeResult;
use Clean\LibraryBundle\Entity\ErrorCodeVersionEntity;
use Symfony\Component\HttpFoundation\Response;

class ErrorCodeController extends BaseController {

	public function errorCodePageListAction() {
		if ($this->CompanyId == -1) {
			$isAdmin = true;
		} else {
			$isAdmin = false;
		}
		$lmcc = $this->get("library_model_clean_company");
		$companyInfo = $lmcc->getEntityList();

		//查看是否有版本号
		$lmcecv = $this->get("library_model_clean_errorcodeversion");
		$versionEntity = $lmcecv->getExistEntity();
		$versionInfo = array(
			"errorCodeVersionId" => $versionEntity->getErrorCodeVersionId(),
			"version" => $versionEntity->getVersion(),
		);
		return $this->render("CleanAdminBundle:ErrorCode:errorCodePageList.html.twig", array("isAdmin" => $isAdmin, "companyInfo" => $companyInfo, "versionInfo" => $versionInfo));
	}

	public function getErrorCodePageListAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$lmcec = $this->get("library_model_clean_errorcode");
			$companyId = intval($this->requestParameter("companyId"));
			$pageIndex = intval($this->requestParameter("pageIndex"));
			if (empty($pageIndex)) {
				$pageIndex = 1;
			}

			$pageSize = intval($this->requestParameter("pageSize"));
			if (empty($pageSize)) {
				$pageSize = 30;
			}
			$result = $lmcec->getPageErrorCode($pageIndex, $pageSize, $companyId);
			if ($result) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
			} else {
				return new Response($this->getAPIResultJson("E02000", "数据读取失败", ""));
			}

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addErrorCodeAction() {
		try
		{
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityList();
			return $this->render("CleanAdminBundle:ErrorCode:addErrorCode.html.twig", array("companyInfo" => $companyInfo));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addErrorCodeSubmitAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			//查看是否有版本号
			$lmcecv = $this->get("library_model_clean_errorcodeversion");
			$versionInfo = $lmcecv->getExistEntity();
			if (!$versionInfo) {
				return new Response($this->getAPIResultJson("E02000", "请先添加版本号", ""));
			}

			$companyId = intval($this->requestParameter("companyId"));
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}

			$code = $this->requestParameter("code");
			$errType = $this->requestParameter("errType");
			$enMsg = $this->requestParameter("enMsg");
			$chMsg = $this->requestParameter("chMsg");
			$koMsg = $this->requestParameter("koMsg");
			if (!$code || !$errType || !$enMsg || !$chMsg || !$koMsg) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcec = $this->get("library_model_clean_errorcode");
			$errorCodeInfo = $lmcec->getEntityByCode($code);
			if ($errorCodeInfo) {
				return new Response($this->getAPIResultJson("E02000", "错误码已存在，请勿重复添加", ""));
			}

			$entity = new errorCodeEntity();
			$entity->setCompanyId($companyId);
			$entity->setCode($code);
			$entity->setErrType($errType);
			$entity->setEnMsg($enMsg);
			$entity->setChMsg($chMsg);
			$entity->setKoMsg($koMsg);
			$entity->setErrorCodeVersionId($versionInfo->getErrorCodeVersionId());

			$errorCodeId = $lmcec->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editErrorCodeAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$errorCodeId = intval($this->requestParameter("errorCodeId"));
			if ($errorCodeId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityList();
			$lmcec = $this->get("library_model_clean_errorcode");
			$errorCodeEntity = $lmcec->getEntity($errorCodeId);
			if (!$errorCodeEntity) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$errorCodeInfo = new ErrorCodeResult();
			$errorCodeInfo->setErrorCodeId($errorCodeEntity->getErrorCodeId());
			$errorCodeInfo->setCompanyId($errorCodeEntity->getCompanyId());
			$errorCodeInfo->setCode($errorCodeEntity->getCode());
			$errorCodeInfo->setErrType($errorCodeEntity->getErrType());
			$errorCodeInfo->setEnMsg($errorCodeEntity->getEnMsg());
			$errorCodeInfo->setChMsg($errorCodeEntity->getChMsg());
			$errorCodeInfo->setKoMsg($errorCodeEntity->getKoMsg());

			$companyId = $errorCodeEntity->getCompanyId();
			if ($companyId == -1) {
				$companyName = '通用';
			} else {
				$companyNameInfo = $lmcc->getEntity($companyId);
				$companyName = $companyNameInfo->getCompanyName();
			}
			$errorCodeInfo->setCompanyName($companyName);

			return $this->render("CleanAdminBundle:ErrorCode:editErrorCode.html.twig", array("companyInfo" => $companyInfo, "errorCodeInfo" => $errorCodeInfo));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editErrorCodeSubmitAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$companyId = intval($this->requestParameter("companyId"));
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}

			$errorCodeId = intval($this->requestParameter("errorCodeId"));
			$code = $this->requestParameter("code");
			$errType = $this->requestParameter("errType");
			$enMsg = $this->requestParameter("enMsg");
			$chMsg = $this->requestParameter("chMsg");
			$koMsg = $this->requestParameter("koMsg");
			if (!$errorCodeId || !$code || !$errType || !$enMsg || !$chMsg || !$koMsg) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$lmcec = $this->get("library_model_clean_errorcode");
			$errorCodeEntity = $lmcec->getEntity($errorCodeId);
			if (!$errorCodeEntity) {
				return new Response($this->getAPIResultJson("E02000", "数据异常", ""));
			}
			$errorCodeEntity->setCompanyId($companyId);
			$errorCodeEntity->setCode($code);
			$errorCodeEntity->setErrType($errType);
			$errorCodeEntity->setEnMsg($enMsg);
			$errorCodeEntity->setChMsg($chMsg);
			$errorCodeEntity->setKoMsg($koMsg);
			$lmcec->editEntity($errorCodeEntity);

			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function deleteErrorCodeListAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$errorCodeIdList = $this->requestParameter("errorCodeIdList");
			if (!$errorCodeIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $errorCodeIdList);
			$lmcec = $this->get("library_model_clean_errorcode");
			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$lmcec->deleteEntity($listArr[$i]);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addErrorCodeVersionAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			//查看是否有版本号
			$lmcecv = $this->get("library_model_clean_errorcodeversion");
			$versionInfo = $lmcecv->getExistEntity();
			if ($versionInfo) {
				return new Response($this->getAPIResultJson("E02000", "已经存在版本号，请修改版本号", ""));
			}
			$version = $this->requestParameter("version");
			if (!$version) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$entity = new ErrorCodeVersionEntity();
			$entity->setVersion($version);
			$intVersionCode = intval(str_replace(".", "", $version));
			$entity->setIntVersion($intVersionCode);
			$lmcecv->addEntity($entity);
			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editErrorCodeVersionAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			//查看是否有版本号
			$errorCodeVersionId = intval($this->requestParameter("errorCodeVersionId"));
			$version = $this->requestParameter("version");
			if (!$version || !$errorCodeVersionId) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$lmcecv = $this->get("library_model_clean_errorcodeversion");
			$versionInfo = $lmcecv->getEntity($errorCodeVersionId);
			$versionInfo->setVersion($version);

			$intVersionCode = intval(str_replace(".", "", $version));
			$versionInfo->setIntVersion($intVersionCode);
			$lmcecv->editEntity($versionInfo);
			return new Response($this->getAPIResultJson("N00000", "修改成功", ""));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

}
?>