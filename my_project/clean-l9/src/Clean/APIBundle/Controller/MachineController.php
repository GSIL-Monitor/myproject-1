<?php

namespace Clean\APIBundle\Controller;

use Clean\APIBundle\Controller\BaseController;
use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\MachineMapResult;
use Clean\LibraryBundle\Entity\MachineCleanRecordEntity;
use Common\Utils\ConfigHandler;
use Common\Utils\Crypt\AESCryptHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class MachineController extends BaseController {

	public function getMachineDataAction() {
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
		if (empty($sn)) {
			return new Response($this->getAPIResultJson("E02000", "暂无数据", ""));
		}

		$lmcmd = $this->get("library_model_clean_machinedata");
		$result = $lmcmd->getMachineDataInfoBySn($sn);

		return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));

	}

    /**
     * 添加老数据到清扫记录表
     */
	public function setMachineSweepingListAction() {
        set_time_limit(0);
        ini_set('memory_limit','256M');
        $filePath = ConfigHandler::getCommonConfig("cleanPath");
        $cleanUrl = ConfigHandler::getCommonConfig("cleanUrl");
        $filesnames = scandir($filePath);

        if ($filesnames){
            $machineMapEntity = new MachineCleanRecordEntity();
            $lmcmm = $this->get("library_model_clean_machinecleanrecord");
            foreach ($filesnames as $k => $v) {
                if ($v == '.' || $v == '..') continue;
                if (strpos($v, "-")) continue;

                $path = $filePath . '/' . $v . '/sweeping';
                $files = scandir($path);
                if ($files) {
                    foreach ($files as $file) {
                        if ($file == '.' || $file == '..') continue;
                        if (!strpos($file, "txt")) continue;
                        $result = [];
                        $filename = pathinfo($file, PATHINFO_FILENAME);
                        $file_res = explode('_', $filename);
                        $res = $lmcmm->getEntityBySnAndSort($v, $file_res[0]);
                        if ($res) continue;
                        $machineMapEntity->setSn($v);
                        $machineMapEntity->setSort($file_res[0]);
                        $machineMapEntity->setCleanArea($file_res[3]);
                        $machineMapEntity->setMopArea($file_res[4]);
                        $machineMapEntity->setStartTime($file_res[1]);
                        $machineMapEntity->setEndTime($file_res[2]);
                        $url = $cleanUrl . "/" . $v . "/sweeping/" . $file;
                        $machineMapEntity->setUrl($url);
                        array_push($result, $machineMapEntity);
                        $lmcmm->addBatchEntity($result);
                        unset($file_res);
                    }
                    unset($result);
                    array_push($tmpt, $v);
                }
                unset($files);
                //sleep(3);
            }
        }
        return new Response('success');
    }

    /**
     * 获取清扫记录列表
     * @return Response
     */
    public function getMachineSweepingListSqlAction()
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
        if (empty($sn)) {
            return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
        }
        $page = intval($this->getParameter("page")) ? intval($this->getParameter("page")) : 1;
        $size = 30;
        $lmcmm = $this->get("library_model_clean_machinecleanrecord");
        $res = $lmcmm->getPageSn($page, $size, $sn);
        $data = [];
        $lmcmm = $this->get("library_model_clean_machinemap");
        foreach ($res as $k => $v) {
            $data[$k]['startTime'] = new \DateTime(date("Y-m-d H:i:s", $v['starttime']));
            $data[$k]['timeLong'] = floor(($v['endtime'] - $v['starttime'])/60);
            $data[$k]['sweepArea'] = $v['cleanarea'];
            $data[$k]['mopArea'] = $v['moparea'];
            $data[$k]['fileName'] = pathinfo($v['url'],PATHINFO_BASENAME);
            $mapEntity = $lmcmm->getMachineMapInfoByUrl($v['url']);
            if ($mapEntity) {
                $data[$k]["isMap"] = 1;
                $data[$k]["backupMd5"] = $mapEntity->getBackupMd5();
                $data[$k]["url"] = $mapEntity->getUrl();
            }else{
                $data[$k]["isMap"] = 0;
                $data[$k]["backupMd5"] = '';
                $data[$k]["url"] = $v['url'];
            }
        }
        return new Response($this->getAPIResultJson("N00000", "数据读取成功", $data));
    }

	//获取清扫记录列表
	public function getMachineSweepingListAction() {
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
			if (empty($sn)) {
				return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
			}
			$page = intval($this->getParameter("page")) ? intval($this->getParameter("page")) : 1;

			$filePath = ConfigHandler::getCommonConfig("cleanPath") . "/" . $sn . "/sweeping";
			if (!file_exists($filePath)) {
				return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
			}
			$filesnames = scandir($filePath);

			unset($filesnames[0]);
			unset($filesnames[1]);
			$count = 30;

			$lmcmm = $this->get("library_model_clean_machinemap");
			$res = array();
			if ($filesnames) {
				foreach ($filesnames as $k => $v) {

					if (!strpos($v, "txt")) {
						continue;
					}
					$tem = explode("_", substr($v, 0, -4));
					//$timeLong = floor(($tem[2]-$tem[1])/60).".".($tem[2]-$tem[1])%60;
					$timeLong = floor(($tem[2] - $tem[1]) / 60);
					$temRes = array(
						"startTime" => new \DateTime(date("Y-m-d H:i:s", $tem[1])),
						"timeLong" => $timeLong,
						"sweepArea" => $tem[3],
						"mopArea" => $tem[4],
						"fileName" => $v,
						"isMap" => 0,
						"backupMd5" => '',
						"url" => "",
					);

					//获取地图数据
					$fileUrl = ConfigHandler::getCommonConfig("cleanUrl") . "/" . $sn . "/sweeping";
					$mapName = substr($v, 0, -3) . "record";

					$mapUrl = $fileUrl . "/" . $mapName;
					$mapEntity = $lmcmm->getMachineMapInfoByUrl($mapUrl);
					if ($mapEntity) {
						$temRes["isMap"] = 1;
						$temRes["backupMd5"] = $mapEntity->getBackupMd5();
						$temRes["url"] = $mapEntity->getUrl();

					}

					$res[$tem[1]] = $temRes;

				}
				krsort($res);
				$result = array_values($res);

				// $i=0;
				// $result = array();
				// foreach($res as $k=>$v)
				// {
				//     $result[$i] = $res[$k];
				//     $i++;
				// }

				$type = intval($this->getParameter("type"));
				if ($type == 1) {
					$fileName = $result[0]["fileName"];
					$filePath = ConfigHandler::getCommonConfig("cleanPath") . "/" . $sn . "/sweeping/" . $fileName;
					$contents = $this->getFileContent($filePath);
					if (!$contents) {
						return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
					} else {
						$tem = substr($contents, 0, -3);
						$msgArr = json_decode($tem, true);
						if ($msgArr["data"]) {
							return new Response($this->getAPIResultJson("N00000", "数据读取成功", $msgArr["data"]));
						} else {
							return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
						}
					}
				}

				$res = array_slice($result, ($page - 1) * $count, 30);

				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $res));
			} else {
				return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//获取清扫记录
	public function getMachineSweepingAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}
			$sn = $this->getParameter("sn");
			$fileName = $this->getParameter("fileName");
			if (!$fileName || !$sn) {
				return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
			}
			$filePath = ConfigHandler::getCommonConfig("cleanPath") . "/" . $sn . "/sweeping/" . $fileName;
			$contents = $this->getFileContent($filePath);
			if (!$contents) {
				return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
			} else {
				$tem = substr($contents, 0, -3);
				$msgArr = json_decode($tem, true);
				if ($msgArr["data"]) {
					return new Response($this->getAPIResultJson("N00000", "数据读取成功", $msgArr["data"]));
				} else {
					return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
				}

			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//删除清扫记录
	public function deleteMachineSweepingAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}
			$sn = $this->getParameter("sn");
			$fileName = $this->getParameter("fileName");
			if (!$fileName || !$sn) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$filePath = ConfigHandler::getCommonConfig("cleanPath") . "/" . $sn . "/sweeping/" . $fileName;

			if (file_exists($filePath)) {
				$contents = $this->getFileContent($filePath);
				if ($contents) {
					$deleteFile = ConfigHandler::getCommonConfig("cleanPath") . "/" . $sn . "/delete/";
					if (!file_exists($deleteFile)) {
						mkdir($deleteFile, 0755, true);
					}
					$deleteFileName = $deleteFile . "/" . $fileName;
					$fp = fopen($deleteFileName, "w");
					fwrite($fp, $contents);
					fclose($fp);

					unlink($filePath);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "数据删除成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//获取地图列表
	public function getMachineMapAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}
			$sn = $this->getParameter("sn");
			if (!$sn) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcmm = $this->get("library_model_clean_machinemap");
			$mapList = $lmcmm->getMachineMapInfoBySn($sn);

			$filePath = ConfigHandler::getCommonConfig("cleanPath");
			$cleanUrl = ConfigHandler::getCommonConfig("cleanUrl");
			$res = array();
			for ($i = 0; $i < count($mapList); $i++) {
				$entity = new MachineMapResult();
				$entity->setSn($mapList[$i]->getSn());
				$entity->setBackupMd5($mapList[$i]->getBackupMd5());
				$entity->setUrl($mapList[$i]->getUrl());
				$entity->setCreateTime($mapList[$i]->getCreateTime());

				//获取清扫数据
				$filename = $filePath . str_replace($cleanUrl, "", $mapList[$i]->getUrl());
				$filename = substr($filename, 0, -6) . "txt";

				$contents = $this->getFileContent($filename);
				$tem = substr($contents, 0, -3);
				$msgArr = json_decode($tem, true);
				$entity->sweeping = $msgArr["data"];

				array_push($res, $entity);
			}

			return new Response($this->getAPIResultJson("N00000", "数据读取成功", $res));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//获取文件内容
	private function getFileContent($filename) {
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			return $content;
		}
		return null;
	}

}
?>