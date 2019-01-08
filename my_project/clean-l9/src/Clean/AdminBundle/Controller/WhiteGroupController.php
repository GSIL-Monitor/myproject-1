<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\WhiteGroupEntity;
use Clean\LibraryBundle\Entity\WhiteGroupSnEntity;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Symfony\Component\HttpFoundation\Response;

class WhiteGroupController extends BaseController {
	//分组列表
	public function whiteGroupPageListAction() {
		$companyId = $this->CompanyId;
		if ($companyId <= 0 && $companyId != -1) {
			return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		}
		return $this->render("CleanAdminBundle:WhiteGroup:whiteGroupPageList.html.twig", array());
	}

	public function getWhiteGroupPageListAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$lmcwg = $this->get("library_model_clean_whitegroup");

			$result = $lmcwg->getEntityList();
			return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addWhiteGroupSubmitAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$groupName = $this->requestParameter("groupName");

			if (!$groupName) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcwg = $this->get("library_model_clean_whitegroup");
			$whiteGroupInfo = $lmcwg->getEntityByName($groupName);
			if ($whiteGroupInfo) {
				return new Response($this->getAPIResultJson("E02000", "分组名称已经存在，请勿重复添加", ""));
			}
			$entity = new WhiteGroupEntity();
			$entity->setGroupName($groupName);
			$entity->setSortId(intval($this->requestParameter("sortId")));
			$WhiteGroupId = $lmcwg->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editWhiteGroupSubmitAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$whiteGroupId = intval($this->requestParameter("whiteGroupId"));
			$groupName = $this->requestParameter("groupName");
			if ($whiteGroupId <= 0 || empty($groupName)) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$lmcwg = $this->get("library_model_clean_whitegroup");
			$whiteGroupInfo = $lmcwg->getEntityByName($groupName);
			if ($whiteGroupInfo) {
				return new Response($this->getAPIResultJson("E02000", "分组名称已经存在，请更换名称", ""));
			}
			$whiteGroupEntity = $lmcwg->getEntity($whiteGroupId);
			$whiteGroupEntity->setGroupName($groupName);

			$whiteGroup = $lmcwg->editEntity($whiteGroupEntity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function deleteWhiteGroupListAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$whiteGroupIdList = $this->requestParameter("whiteGroupIdList");
			if (!$whiteGroupIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $whiteGroupIdList);
			$lmcwg = $this->get("library_model_clean_whitegroup");
			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$lmcwg->deleteEntity($listArr[$i]);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	//获取白名单列表
	public function whiteGroupSnPageListAction() {
		$companyId = $this->CompanyId;
		if ($companyId <= 0 && $companyId != -1) {
			return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		}
		$whiteGroupId = $this->requestParameter("whiteGroupId");
		$whiteGroupName = $this->requestParameter("whiteGroupName");
		return $this->render("CleanAdminBundle:WhiteGroupSn:whiteGroupSnPageList.html.twig", array(
			"whiteGroupId" => $whiteGroupId,
			"whiteGroupName" => $whiteGroupName
		));
	}

	public function getWhiteGroupSnPageListAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$whiteGroupId = $this->requestParameter("whiteGroupId");
			if ($whiteGroupId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$sn = $this->requestParameter("sn");

			$lmcwgs = $this->get("library_model_clean_whitegroupsn");
			$result = $lmcwgs->getEntityListByWhiteGroupIdAndSn($whiteGroupId, $sn);
			return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addWhiteGroupSnSubmitAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$sn = $this->requestParameter("sn");
			$whiteGroupId = $this->requestParameter("whiteGroupId");
			if (!$sn || !$whiteGroupId || strlen($sn) != 16) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcwgs = $this->get("library_model_clean_whitegroupsn");
			$whiteGroupSnInfo = $lmcwgs->getEntityBySnAndWhiteGroupId($sn, $whiteGroupId);
			if ($whiteGroupSnInfo) {
				return new Response($this->getAPIResultJson("E02000", "sn已经存在，请勿重复添加", ""));
			}
			$entity = new WhiteGroupSnEntity();
			$entity->setSn($sn);
			$entity->setWhiteGroupId($whiteGroupId);
			$entity->setNoteName($this->requestParameter("noteName"));
			$WhiteGroupSnId = $lmcwgs->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editWhiteGroupSnSubmitAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$whiteGroupSnId = intval($this->requestParameter("whiteGroupSnId"));
			$sn = $this->requestParameter("sn");
			if ($whiteGroupSnId <= 0 || empty($sn)) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$lmcwgs = $this->get("library_model_clean_whitegroupsn");

			$whiteGroupSnEntity = $lmcwgs->getEntity($whiteGroupSnId);

			$whiteGroupSnInfo = $lmcwgs->getEntityBySnAndWhiteGroupId($sn, $whiteGroupId);
			if ($sn != $whiteGroupSnEntity->getSN() && $whiteGroupSnInfo) {
				return new Response($this->getAPIResultJson("E02000", "sn已经存在，请更改", ""));
			}

			$whiteGroupSnEntity->setSn($sn);
			$whiteGroupSnEntity->setNoteName($this->requestParameter("noteName"));

			$whiteGroupSn = $lmcwgs->editEntity($whiteGroupSnEntity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function deleteWhiteGroupSnListAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$whiteGroupSnIdList = $this->requestParameter("whiteGroupSnIdList");
			if (!$whiteGroupSnIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $whiteGroupSnIdList);
			$lmcwgs = $this->get("library_model_clean_whitegroupsn");
			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$lmcwgs->deleteEntity($listArr[$i]);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	//sn导入
	public function uploadWhiteGroupSnExcelAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$whiteGroupId = $this->requestParameter("whiteGroupId");
			if (!$whiteGroupId) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			//先上传excel
			$filePath = ConfigHandler::getCommonConfig("whiteGroupExcelPath");

			$fileResult = UploadFileHandler::requestUploadTypeFile("file", $filePath, true);
			if (!is_array($fileResult)) {
				return new Response($this->getAPIResultJson("E02000", $fileResult, ""));
			}
			$fileName = $fileResult["fileName"];
			$fileResult = $this->getFileToArray($fileName, $whiteGroupId, true);

			if ($fileResult) {
				$lmcwgs = $this->get("library_model_clean_whitegroupsn");
				$lmcwgs->addBatchEntity($fileResult);
			}

		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}

		return new Response($this->getAPIResultJson("N00000", "数据插入成功", ""));
	}

	private function getFileToArray($fileName = '', $whiteGroupId = 0, $hasHead = false) {
		if (empty($fileName) || !$whiteGroupId) {
			return null;
		}

		$fileType = \PHPExcel_IOFactory::identify($fileName); // 文件名自动判断文件类型
		$objReader = \PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($fileName);

		$currentSheet = $objPHPExcel->getSheet(0); // 第一个工作簿
		$allRow = $currentSheet->getHighestRow(); // 行数

		$startRowIndex = 1;
		if ($hasHead) {
			$startRowIndex += 1;
		}
		$allColumn = $currentSheet->getHighestColumn(); // 列数

		$result = array();
		$lmcwgs = $this->get("library_model_clean_whitegroupsn");
		for ($currentRow = $startRowIndex; $currentRow <= $allRow; $currentRow++) {
			$sn = $currentSheet->getCell('A' . $currentRow)->getValue();
			if (!$sn) {
				return null;
			}

			$whiteGroupSnInfo = $lmcwgs->getEntityBySnAndWhiteGroupId($sn);

			if (!$whiteGroupSnInfo) {
				$noteName = $currentSheet->getCell('B' . $currentRow)->getValue();
				$entity = new WhiteGroupSnEntity();
				$entity->setSn($sn);
				$entity->setWhiteGroupId($whiteGroupId);
				$entity->setNoteName($noteName);
				array_push($result, $entity);
			}

		}
		return $result;
	}

}
?>