<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\MachineEntity;
use Clean\LibraryBundle\Entity\MachineKeyEntity;
use Common\Utils\Crypt\RSAHandler;
use Symfony\Component\HttpFoundation\Response;

class MachineLogController extends BaseController {

	public function machineLogPageListAction() {
		$lmcc = $this->get("library_model_clean_company");
		if ($this->CompanyId == -1) {
			$isAdmin = true;
		} else {
			$isAdmin = false;
		}
		$sn = $this->requestParameter("sn");
		return $this->render("CleanAdminBundle:MachineLog:machineLogPageList.html.twig", array(
			"isAdmin" => $isAdmin,
			"sn" => $sn,
		));
	}

	public function getMachineLogPageListAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$sn = $this->requestParameter("sn");

			if (!$sn) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$type = $this->requestParameter("type");
			$startDate = $this->requestParameter("startDate");
			$endDate = $this->requestParameter("endDate");

			$lmcml = $this->get("library_model_clean_machinelog");
			$pageIndex = intval($this->requestParameter("pageIndex"));
			if (empty($pageIndex)) {
				$pageIndex = 1;
			}

			$pageSize = intval($this->requestParameter("pageSize"));
			if (empty($pageSize)) {
				$pageSize = 30;
			}
			$result = $lmcml->getPageMachineLog($pageIndex, $pageSize, $type, $sn, $startDate, $endDate);
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

	//导入数据到 machine_key
	public function uploadMachineExcelAction() {
		if ($this->CompanyId != -1) {
			return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		}
		return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		$fileName = "/mnt/www/file/snkey/1.csv";

		//批量生成key
		$fileResult = $this->getSnKeyFileToArray($fileName, true);

		//批量导入sn
		//$fileResult = $this->getSnFileToArray($fileName, true);
		$lmcmk = $this->get("library_model_clean_machinekey");
		$lmcmk->addBatchEntity($fileResult);

		return new Response($this->getAPIResultJson("N00000", "数据插入成功", ""));
	}

	private function getSnFileToArray($fileName, $hasHead = false) {
		if (empty($fileName)) {
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

		$drawing = new \PHPExcel_Writer_Excel2007_Drawing();
		$drawingHashTable = new \PHPExcel_HashTable();

		$result = array();
		$lmcm = $this->get("library_model_clean_machine");

		for ($currentRow = $startRowIndex; $currentRow <= $allRow; $currentRow++) {
			$sn = $currentSheet->getCell('A' . $currentRow)->getValue();

			//先判断是否存在
			$machineEntity = $lmcm->getMachineBySn($sn);

			if (!$machineEntity) {
				$entity = new MachineEntity();
				$entity->setSn($sn);
				$entity->setCompanyId(15);
				$entity->setMachineType(15);
				array_push($result, $entity);
			}

		}
		return $result;
	}

	//导出数据到
	public function getMachineExcelAction() {
		if ($this->CompanyId != -1) {
			return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		}

		return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		$lmcmk = $this->get("library_model_clean_machinekey");
		$dataList = $lmcmk->getExcelMachineKey();

		$this->outputDataToExcel($dataList);

		return new Response($this->getAPIResultJson("N00000", "数据读取成功", ""));
	}

	private function getSnKeyFileToArray($fileName, $hasHead = false) {
		if (empty($fileName)) {
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

		$drawing = new \PHPExcel_Writer_Excel2007_Drawing();
		$drawingHashTable = new \PHPExcel_HashTable();

		$result = array();

		for ($currentRow = $startRowIndex; $currentRow <= $allRow; $currentRow++) {
			$sn = $currentSheet->getCell('A' . $currentRow)->getValue();
			$entity = new MachineKeyEntity();
			$key = RSAHandler::createKey();
			$entity->setSn($sn);
			$entity->setPublicKey($key["public_key"]);
			$entity->setPrivateKey($key["private_key"]);

			array_push($result, $entity);
		}
		return $result;
	}

	private function outputDataToExcel($dataList) {
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("sn")->setLastModifiedBy("sn")->setTitle('sn')->setSubject('sn')->setDescription('sn')->setKeywords('sn')->setCategory('sn');
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'sn')
			->setCellValue('B1', 'key')
		;

		$objPHPExcel->getActiveSheet()
			->getStyle('A1:A1000')
			->getNumberFormat()
			->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
		$currRow = 2;
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('80');
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(80);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$objPHPExcel->getActiveSheet()->getStyle('B1:B1000')->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(false);

		foreach ($dataList as $key => $val) {

			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $currRow, $val->getSn())
				->setCellValue('B' . $currRow, $val->getPublicKey())
			//->setCellValue('C' . $currRow, $val->getPrivateKey())
			;

			$currRow++;
		}

		$objPHPExcel->getActiveSheet()->setTitle('sn');
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=sn" . date("YmdHis") . ".csv");
		$objWriter->save('php://output');
		exit();
	}

}
?>