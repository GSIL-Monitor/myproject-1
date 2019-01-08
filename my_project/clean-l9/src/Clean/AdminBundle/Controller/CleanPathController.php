<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Common\Utils\ConfigHandler;
use Common\Utils\File\FileCommonHandler;
use Symfony\Component\HttpFoundation\Response;

class CleanPathController extends BaseController {

	public function getPathAction() {

		if ($this->CompanyId != -1) {
			return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		}
		$sn = $this->requestParameter("sn") ? $this->requestParameter("sn") : "";
		$dir = ConfigHandler::getCommonConfig("cleanPath");
		if ($sn && strlen($sn) == 16) {
			$fileName = $dir . "/" . $sn . "/sweeping";
			$fileArr = scandir($fileName);
			$result = array();
			for ($i = 0; $i < count($fileArr); $i++) {
				if ($fileArr[$i] != "." && $fileArr[$i] != ".." && strlen($fileArr[$i]) > 30) {

					if (!strpos($fileArr[$i], "txt")) {
						continue;
					}

					$arr = array();
					$cleanUrl = ConfigHandler::getCommonConfig("cleanUrl");
					$arr["filename"] = $fileArr[$i];
					$arr["url"] = $cleanUrl . "/" . $sn . "/sweeping/" . $fileArr[$i];
					$path = $dir . "/" . $sn . "/sweeping/" . $fileArr[$i];
					//$arr["createTime"] =  date( "Y-m-d H:i:s", filemtime ( $path ));
					$tem = explode("_", substr($fileArr[$i], 0, -4));
					$arr["createTime"] = date("Y-m-d H:i:s", $tem[1]);
					$result[$tem[1]] = $arr;
					krsort($result);
				}
			}

		} else {
			$result = array();
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						if ($file != "." && $file != ".." && strlen($file) == 16) {
							$arr["filename"] = $file;
							$path = $dir . "/" . $sn;
							$arr["createTime"] = date("Y-m-d H:i:s", filemtime($path));
							array_push($result, $arr);
						}
					}
					closedir($dh);
				}
			}
		}

		return $this->render("CleanAdminBundle:CleanPath:cleanPathList.html.twig", array(
			"arr" => $result,
			"sn" => $sn,
		));
	}

	//获得解压的文件
	public function getPathZipAction() {
		if ($this->CompanyId != -1) {
			return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		}
		$isAll = $this->requestParameter("isAll");
		if ($isAll == 1) {
			$resultZip = '';
			exec('tar -czvf  /var/www/upload/cleanfile/path.tar.gz  /var/www/upload/cleanfile', $resultZip);
			if (is_array($resultZip) && $resultZip) {
				$url = ConfigHandler::getCommonConfig("cleanUrl") . "/path.tar.gz";
				return new Response($this->getAPIResultJson("N00000", "数据解压成功", $url));
			} else {
				return new Response($this->getAPIResultJson("E02000", "数据解压失败", ""));
			}

		} else {
			$sn = $this->requestParameter("sn");
			if ($sn && strlen($sn) >= 16) {
				$arr = explode(",", $sn);
				$snTem = '';
				for ($i = 0; $i < count($arr); $i++) {
					if (strlen($arr[$i]) == 16) {
						if (file_exists(ConfigHandler::getCommonConfig("cleanPath") . "/" . $arr[$i])) {
							$snTem .= ConfigHandler::getCommonConfig("cleanPath") . "/" . $arr[$i] . " ";
						}
					}
				}

				if ($snTem) {
					$resultZip = '';

					exec("tar -czvf  /var/www/upload/cleanfile/path.tar.gz  {$snTem}", $resultZip);

					if (is_array($resultZip) && $resultZip) {
						$url = ConfigHandler::getCommonConfig("cleanUrl") . "/path.tar.gz";
						return new Response($this->getAPIResultJson("N00000", "数据解压成功", $url));
					} else {
						return new Response($this->getAPIResultJson("E02000", "数据解压失败", ""));
					}
				} else {
					return new Response($this->getAPIResultJson("E02000", "暂无数据", ""));
				}

			} else {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
		}

	}

	// //删除文件
	public function deletePathAction() {

		if ($this->CompanyId != -1) {
			return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		}
		$type = intval($this->requestParameter("type"));
		if (!$type) {
			return new Response($this->getAPIResultJson("E02000", "缺少数据", ""));
		}

		$isAll = intval($this->requestParameter("isAll"));
		$cleanPath = ConfigHandler::getCommonConfig("cleanPath");
		if ($type == 1) {
			//删除清扫记录文件
			$sn = $this->requestParameter("sn");
			if (!$sn || strlen($sn) != 16) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}

			if ($isAll == 1) {
				//删除全部
				$deleteDir = $cleanPath . "/" . $sn . "/sweeping";
				if ($deleteDir) {
					FileCommonHandler::deleteFiles($deleteDir);
				}
			} else {
				$filenames = $this->requestParameter("filenames");
				if (!$filenames || strlen($filenames) <= 30) {
					return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
				}
				$fileArr = explode(",", $filenames);
				for ($i = 0; $i < count($fileArr); $i++) {
					$filePath = $cleanPath . "/" . $sn . "/sweeping/" . $fileArr[$i];
					if (file_exists($filePath)) {
						unlink($filePath);
					}
				}

			}

		} elseif ($type == 2) {
			//删除文件
			$snFile = $this->requestParameter("snFile");
			if (!$snFile || strlen($snFile) < 16) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$fileArr = explode(",", $snFile);
			for ($i = 0; $i < count($fileArr); $i++) {
				$filePath = $cleanPath . "/" . $fileArr[$i] . "/sweeping/";
				if (file_exists($filePath)) {
					FileCommonHandler::deleteFiles($filePath);
				}
			}

		}
		return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
	}

	public function mapFilePathAction() {
		if ($this->CompanyId != -1) {
			return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
		}
		$sn = $this->requestParameter("sn");
		$file = $this->requestParameter("file");
		$dir = ConfigHandler::getCommonConfig("cleanPath");
		if (!$sn || strlen($sn) < 16 || !$file) {
			return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
		}

		$fileName = $dir . "/" . $sn . "/sweeping";
		$fileName = $fileName . '/' . $file;
		$content = fopen($fileName, "r");
		$json = fread($content, filesize($fileName));
		fclose($content);
		$json = preg_replace('/\#\s*\#/', '', $json);
		echo ($json);
		die();
		exit();
	}

}
?>