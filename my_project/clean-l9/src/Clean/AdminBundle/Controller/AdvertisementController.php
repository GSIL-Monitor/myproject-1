<?php
namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\AdvertisementEntity;
use Clean\LibraryBundle\Entity\AdvertisementPlaceEntity;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Symfony\Component\HttpFoundation\Response;

class AdvertisementController extends BaseController {

	public function advertisementPlacePageListAction() {
		return $this->render("CleanAdminBundle:Advertisement:advertisementPlaceList.html.twig", array());
	}

	public function getAdvertisementPlacePageListAction() {
		try
		{

			$lmap = $this->get("library_model_clean_advertisementplace");
			$dataList = $lmap->getAdvertisementPlaceList();
			return new Response($this->getAPIResultJson("N00000", "数据读取成功", $dataList));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function addAdvertisementPlaceAction() {
		return $this->render("CleanAdminBundle:Advertisement:addAdvertisementPlace.html.twig", array());
	}

	public function addAdvertisementPlaceSubmitAction() {
		try
		{

			$placeName = $this->requestParameter("placeName");
			$flag = $this->requestParameter("flag");

			$entity = new AdvertisementPlaceEntity();
			$entity->setPlaceName($placeName);
			$entity->setFlag($flag);
			$entity->setDescription($this->requestParameter("description"));
			$entity->setPlaceHeight(intval($this->requestParameter("placeHeight")));
			$entity->setPlaceWidth(intval($this->requestParameter("placeWidth")));
			$entity->setAdminUserId($this->LoginUserId);

			if (empty($placeName) || empty($flag)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", $entity));
			}

			$lmap = $this->get("library_model_clean_advertisementplace");
			if ($lmap->isExistAdvertisementPlaceByFlag($flag)) {
				return new Response($this->getAPIResultJson("E02000", "该数据已存在", $entity));
			}

			$result = $lmap->addAdvertisementPlace($entity);

			return new Response($this->getAPIResultJson("N00000", "数据添加成功", $result));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function editAdvertisementPlaceAction() {
		$advertisementPlaceId = intval($this->requestParameter("advertisementPlaceId"));
		$lmap = $this->get("library_model_clean_advertisementplace");
		$entity = $lmap->getAdvertisementPlace($advertisementPlaceId);
		return $this->render("CleanAdminBundle:Advertisement:editAdvertisementPlace.html.twig", array(
			"entity" => $entity,
		));
	}

	public function editAdvertisementPlaceSubmitAction() {
		try
		{

			$advertisementPlaceId = intval($this->requestParameter("advertisementPlaceId"));
			$placeName = $this->requestParameter("placeName");
			$flag = $this->requestParameter("flag");

			$lmap = $this->get("library_model_clean_advertisementplace");
			$entity = $lmap->getAdvertisementPlace($advertisementPlaceId);

			if (!$entity) {
				return new Response($this->getAPIResultJson("E02000", "数据异常", ""));
			}

			if ($flag != $entity->getFlag() && $lmap->isExistAdvertisementPlaceByFlag($flag)) {
				return new Response($this->getAPIResultJson("E02000", "该数据已存在", $entity));
			}

			$entity->setPlaceName($placeName);
			$entity->setFlag($flag);
			$entity->setPlaceHeight(intval($this->requestParameter("placeHeight")));
			$entity->setPlaceWidth(intval($this->requestParameter("placeWidth")));
			$entity->setDescription($this->requestParameter("description"));

			if (empty($placeName) || empty($flag)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", $entity));
			}

			$result = $lmap->editAdvertisementPlace($entity);

			return new Response($this->getAPIResultJson("N00000", "数据修改成功", $result));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function deleteAdvertisementPlaceListAction() {
		try
		{
			$advertisementPlaceIdList = $this->requestParameter("advertisementPlaceIdList");
			if (!$advertisementPlaceIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $advertisementPlaceIdList);
			$lmap = $this->get("library_model_clean_advertisementplace");
			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$lmap->deleteAdvertisementPlace($listArr[$i]);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function advertisementPageListAction() {

		$lmap = $this->get("library_model_clean_advertisementplace");
		$placeList = $lmap->getAdvertisementPlaceList();
		return $this->render("CleanAdminBundle:Advertisement:advertisementPageList.html.twig", array(
			"placeList" => $placeList,
		));
	}

	public function getAdvertisementPageListAction() {
		try
		{

			$advertisementPlaceId = intval($this->requestParameter("advertisementPlaceId"));
			$keyword = $this->requestParameter("keyword");

			$pageIndex = intval($this->requestParameter("pageIndex"));
			if (empty($pageIndex)) {
				$pageIndex = 1;
			}

			$pageSize = intval($this->requestParameter("pageSize"));
			if (empty($pageSize)) {
				$pageSize = 30;
			}

			$lma = $this->get("library_model_clean_advertisement");
			$dataList = $lma->getPageAdvertisement($advertisementPlaceId, $keyword, $pageIndex, $pageSize);
			if ($dataList) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $dataList));
			}
			return new Response($this->getAPIResultJson("E02000", "数据读取失败", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function addAdvertisementAction() {

		$lmap = $this->get("library_model_clean_advertisementplace");
		$placeList = $lmap->getAdvertisementPlaceList();
		return $this->render("CleanAdminBundle:Advertisement:addAdvertisement.html.twig", array(
			"placeList" => $placeList,
		));
	}

	public function addAdvertisementSubmitAction() {
		try
		{

			$title = $this->requestParameter("title");
			$advertisementPlaceId = intval($this->requestParameter("advertisementPlaceId"));
			$fileUrl = $this->requestParameter("fileUrl");
			if (!empty($fileUrl)) {
				$advertisementUrl = ConfigHandler::getCommonConfig("advertisementUrl");
				$fileUrl = str_replace($advertisementUrl, "", $fileUrl);
			}
			$language = $this->requestParameter("language");

			$entity = new AdvertisementEntity();
			$entity->setTitle($title);
			$entity->setFileUrl($fileUrl);
			$entity->setUrl($this->requestParameter("url"));
			$entity->setSortId(intval($this->requestParameter("sortId")));
			$entity->setAdvertisementPlaceId($advertisementPlaceId);
			$entity->setDescription($this->requestParameter("description"));
			$entity->setLanguage($language);
			$entity->setAdminUserId($this->LoginUserId);


			// if (empty($title) || empty($startTime) || empty($endTime) || empty($advertisementPlaceId) || empty($fileUrl)) {
			// 	return new Response($this->getAPIResultJson("E02000", "数据填写不完整", $entity));
			// }

			if (empty($title) || empty($advertisementPlaceId) || empty($fileUrl)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", $entity));
			}


			$lma = $this->get("library_model_clean_advertisement");

			$result = $lma->addAdvertisement($entity);

			return new Response($this->getAPIResultJson("N00000", "数据添加成功", $result));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function uploadAdvertisementFileAction() {
		$filePath = ConfigHandler::getCommonConfig("advertisementPath");
		$fileResult = UploadFileHandler::requestUploadTypeFile("advertisementFile", $filePath, true);
		if (!is_array($fileResult)) {
			return new Response($this->getAPIResultJson("E02000", $fileResult, ""));
		}

		$filename = $fileResult["filename"];
		$url = str_replace($filePath, "", $filename);
		$advertisementUrl = ConfigHandler::getCommonConfig("advertisementUrl");
		$url = $advertisementUrl . $url;
		return new Response($this->getAPIResultJson("N00000", "上传成功", $url));
	}

	public function editAdvertisementAction() {

		$advertisementId = intval($this->requestParameter("advertisementId"));
		$lma = $this->get("library_model_clean_advertisement");
		$entity = $lma->getAdvertisement($advertisementId);
		if (!empty($entity)) {
			$advertisementUrl = ConfigHandler::getCommonConfig("advertisementUrl");
			$fileUrl = $entity->getFileUrl();
			$entity->setFileUrl($advertisementUrl . $fileUrl);
		}

		$lmap = $this->get("library_model_clean_advertisementplace");
		$placeList = $lmap->getAdvertisementPlaceList();

		return $this->render("CleanAdminBundle:Advertisement:editAdvertisement.html.twig", array(
			"entity" => $entity,
			"placeList" => $placeList,
		));
	}

	public function editAdvertisementSubmitAction() {
		try
		{

			$advertisementId = intval($this->requestParameter("advertisementId"));

			$title = $this->requestParameter("title");
			$advertisementPlaceId = intval($this->requestParameter("advertisementPlaceId"));
			$fileUrl = $this->requestParameter("fileUrl");
			if (!empty($fileUrl)) {
				$advertisementUrl = ConfigHandler::getCommonConfig("advertisementUrl");
				$fileUrl = str_replace($advertisementUrl, "", $fileUrl);
			}
			$language = $this->requestParameter("language");

			$lma = $this->get("library_model_clean_advertisement");
			$entity = $lma->getAdvertisement($advertisementId);

			if (!$entity) {
				return new Response($this->getAPIResultJson("E02000", "数据异常", ""));
			}

			$entity->setTitle($title);
			$entity->setFileUrl($fileUrl);
			$entity->setUrl($this->requestParameter("url"));
			$entity->setSortId(intval($this->requestParameter("sortId")));
			$entity->setAdvertisementPlaceId($advertisementPlaceId);
			$entity->setDescription($this->requestParameter("description"));
			$entity->setLanguage($language);

			// if (empty($title) || empty($startTime) || empty($endTime) || empty($advertisementPlaceId) || empty($fileUrl)) {
			// 	return new Response($this->getAPIResultJson("E02000", "数据填写不完整", $entity));
			// }
			if (empty($title) || empty($advertisementPlaceId) || empty($fileUrl)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", $entity));
			}

			$result = $lma->editAdvertisement($entity);

			return new Response($this->getAPIResultJson("N00000", "数据修改成功", $result));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function deleteAdvertisementListAction() {
		try
		{
			$advertisementIdList = $this->requestParameter("advertisementIdList");
			if (!$advertisementIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $advertisementIdList);
			$lma = $this->get("library_model_clean_advertisement");
			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$lma->deleteAdvertisement($listArr[$i]);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

}