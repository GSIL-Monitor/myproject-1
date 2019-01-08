<?php

namespace Clean\APIBundle\Controller;

use Clean\APIBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\OQCEntity;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class OQCController extends BaseController {

	public function __construct()
    {
        $this->ADMIN_USERNAME = 'admin';
        $this->ADMIN_PASSWORD = 'robot-check-oqc';
    }

	public function checkAction() {		
		define('DATE_FORMAT', 'Y/m/d H:i:s');
		define('GRAPH_SIZE', 200);
		define('MAX_ITEM_DUMP', 50);
		if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
		    $_SERVER['PHP_AUTH_USER'] != $this->ADMIN_USERNAME || $_SERVER['PHP_AUTH_PW'] != $this->ADMIN_PASSWORD
		) {
		    Header("WWW-Authenticate: Basic realm=\"Login\"");
		    Header("HTTP/1.0 401 Unauthorized");

		    echo '<html><body>
		          `      <h1>登录失败!</h1>
		           `     <big>用户名或密码错误!</big>
		            `    </body></html>';		
		    exit;
		}else{
			$session = new Session();
			$session->start();
			$session->set("checkName", $this->ADMIN_USERNAME);
		}
		return $this->render("CleanAPIBundle:OQC:index.html.twig", array());
	}

	public function checkSubmitAction() {
		$checkName = $this->requestParameter("checkName", true);

		if (empty($checkName)) {
			return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
		}
		$session = new Session();
		$session->start();

		$checkNameVilid = md5(md5("robot-check-oqc"));
		if ($checkName != $checkNameVilid) {
			return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
		}

		$session->set("checkName", $this->ADMIN_USERNAME);

		return new Response($this->getAPIResultJson("N00000", "登陆成功", ""));

	}

	public function OQCAction() {

		$session = new Session();
		$session->start();
		$checkName = $session->get("checkName");
		if (empty($checkName) || $checkName != $this->ADMIN_USERNAME) {
			return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
		}

		$sn = $this->requestParameter("sn", true);
		if (empty($sn)) {
			return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
		}

		$this->deleteSnInfo($sn);

		return new Response($this->getAPIResultJson("N00000", "重置成功", ""));

	}

	//sn导入
	public function uploadOQCSnExcelAction() {
		try
		{
			$session = new Session();
			$session->start();
			$checkName = $session->get("checkName");
			if (empty($checkName) || $checkName != $this->ADMIN_USERNAME) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			//先上传excel
			$filePath = ConfigHandler::getCommonConfig("OQCExcelPath");

			$fileResult = UploadFileHandler::requestUploadTypeFile("file", $filePath, true);
			if (!is_array($fileResult)) {
				return new Response($this->getAPIResultJson("E02000", $fileResult, ""));
			}
			$fileName = $fileResult["fileName"];
			$this->getFileToArray($fileName, true);
			return new Response($this->getAPIResultJson("N00000", "重置成功", ""));

		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}

	}

	//展示已经销毁数据
	public function OQCListAction() {

		return $this->render("CleanAPIBundle:OQC:OQCList.html.twig", array());
	}

	//展示已经销毁数据
	public function getOQCListAction() {

		$session = new Session();
		$session->start();
		$checkName = $session->get("checkName");
		if (empty($checkName) || $checkName != $this->ADMIN_USERNAME) {
			return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
		}

		$lmcq = $this->get("library_model_clean_OQC");
		$list = $lmcq->getEntityListByTime();
		return new Response($this->getAPIResultJson("N00000", "获取成功", $list));
	}

	private function getFileToArray($fileName = '', $hasHead = false) {
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

		$lmcwgs = $this->get("library_model_clean_whitegroupsn");
		for ($currentRow = $startRowIndex; $currentRow <= $allRow; $currentRow++) {
			$sn = $currentSheet->getCell('A' . $currentRow)->getValue();
			if (!$sn) {
				return null;
			}

			$this->deleteSnInfo($sn);

		}
		return true;
	}

	private function deleteSnInfo($sn) {

		if (!$sn || strlen($sn) != 16) {
			return false;
		}

		$lmcm = $this->get("library_model_clean_machine");
		$machineInfo = $lmcm->getMachineBySn($sn);
		if (!$machineInfo) {
			return false;
		}

		//删除订阅sn
		$lmcu = $this->get("library_model_clean_userinfo");
		$lmcu->deleteNowSnBySn($sn);

		//1.删除绑定记录
		$lmcum = $this->get("library_model_clean_usermachine");
		$lmcum->deleteAllUserMachine($sn);

		//2.删除清扫记录(修改文件名称)
		$filePath = ConfigHandler::getCommonConfig("cleanPath");
		$fileName = $filePath . "/" . $sn;
		if (file_exists($fileName)) {
			$editName = $filePath . "/" . $sn . "-backup" . time();
			rename($fileName, $editName);
		}

		//3.删除socket连接
		$this->deleteAllUser($sn);

		//4.伪删除扫地机总记录
		$lmcmd = $this->get("library_model_clean_machinedata");
		$machineDataInfo = $lmcmd->getMachineDataInfoBySn($sn);
		if ($machineDataInfo) {
			$lmcmd->deleteEntity($machineDataInfo->getMachineDataId());
		}

		//5.添加清除记录
		$companyId = intval($machineInfo->getCompanyId());
		$OQCEntity = new OQCEntity();
		$OQCEntity->setCompanyId($companyId);
		$OQCEntity->setSn($sn);
		$lmcq = $this->get("library_model_clean_OQC");
		$lmcq->addEntity($OQCEntity);

		return true;
	}

	/*
		        * 获取用户信息文件路径
	*/
	private function getRobotFileName($sn) {
		$subDir = substr($sn, 0, 2);
		$dir = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/robots/" . $subDir;
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$filename = $dir . "/" . $sn;
		return $filename;
	}

	private function deleteAllUser($sn) {
		$filename = $this->getRobotFileName($sn);
		if ($filename) {
			//获取文件的内容
			$arr = $this->getRobot($filename);
			//修改文件的内容
			if ($arr) {
				$arr["userList"] = array();
				$fp = fopen($filename, "w");
				fwrite($fp, json_encode($arr));
				fclose($fp);
			}
		}

		return true;
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

}
?>