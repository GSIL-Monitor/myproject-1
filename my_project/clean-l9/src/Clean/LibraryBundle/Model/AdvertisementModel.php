<?php
namespace Clean\LibraryBundle\Model;
use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\AdvertisementEntity;
use Clean\LibraryBundle\Entity\AdvertisementResult;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Model\Common\Paginator;
use Common\Utils\ConfigHandler;

class AdvertisementModel extends BaseModel {
	private function getResponsity($entity = "CleanLibraryBundle:AdvertisementEntity") {
		return $this->entityManager->getRepository($entity);
	}

	public function getAdvertisement($advertisementId) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"advertisementId" => $advertisementId,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;
	}

	public function getCurrentAdvertisementList($advertisementPlaceId, $language = CommonDefine::CHINESE) {
		if (empty($advertisementPlaceId)) {
			return null;
		}
		$whereStr = "a.status = " . CommonDefine::DATA_STATUS_NORMAL;
		$whereStr .= " and a.advertisementPlaceId = " . intval($advertisementPlaceId);
		$paramArr = array();

		$whereStr .= " and a.language=:language";
		$paramArr["language"] = $language;

		$query = $this->getResponsity()->createQueryBuilder("a")
			->where($whereStr)
			->setParameters($paramArr)
			->addOrderBy("a.sortId", "ASC")
			->addOrderBy("a.advertisementId", "DESC");
		$result = $query->getQuery()->getResult();
		if (!empty($result)) {
			$path = ConfigHandler::getCommonConfig("advertisementUrl");
			for ($i = 0; $i < count($result); $i++) {
				$result[$i]->setFileUrl($path . $result[$i]->getFileUrl());
			}
		}

		return $result;
	}

	public function getAdvertisementList($advertimentPlaceId = 0) {
		$whereArr = array(
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		);
		if (!empty($advertimentPlaceId)) {
			$whereArr["advertimentPlaceId"] = $advertimentPlaceId;
		}

		$list = $this->getResponsity()->findBy(
			$whereArr,
			array(
				"sortId" => "ASC",
				"advertisementId" => "DESC",
			)
		);
		if (!empty($list)) {
			$advertisementUrl = ConfigHandler::getCommonConfig("advertisementUrl");
			for ($i = 0; $i < count($list); $i++) {
				$fileUrl = $list[$i]->getFileUrl();
				if (!empty($fileUrl)) {
					$fileUrl = $advertisementUrl . $fileUrl;
				}
				$list[$i]->setFileUrl($fileUrl);
			}
		}
		return $list;
	}

	public function getPageAdvertisement($advertisementPlaceId, $keyword, $pageIndex, $pageSize) {
		$whereStr = "a.status = " . CommonDefine::DATA_STATUS_NORMAL;
		$paramArr = array();
		if (!empty($advertisementPlaceId)) {
			$whereStr .= " and a.advertisementPlaceId = :advertisementPlaceId";
			$paramArr["advertisementPlaceId"] = $advertisementPlaceId;
		}
		if (!empty($keyword)) {
			$whereStr .= " and a.title like :keyword";
			$paramArr["keyword"] = "%" . $keyword . "%";
		}

		$whereStr1 = " and ap.status = " . CommonDefine::DATA_STATUS_NORMAL;

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('a,ap.placeName')
			->from('Clean\LibraryBundle\Entity\AdvertisementEntity', 'a')
			->leftJoin(
				'Clean\LibraryBundle\Entity\AdvertisementPlaceEntity',
				'ap',
				\Doctrine\ORM\Query\Expr\Join::WITH,
				'a.advertisementPlaceId = ap.advertisementPlaceId' . $whereStr1
			)
			->where($whereStr)
			->setParameters($paramArr)
			->orderBy('a.advertisementId', 'DESC');

		try
		{
			$page = new Paginator();
			$data = $page->paginate($query, $pageIndex, $pageSize);

			$advertisementUrl = ConfigHandler::getCommonConfig("advertisementUrl");
			$dataResult = array();
			for ($i = 0; $i < count($data); $i++) {
				$tempResult = new AdvertisementResult();
				$tempResult->setAdminUserId($data[$i][0]->getAdminUserId());
				$tempResult->setAdvertisementId($data[$i][0]->getAdvertisementId());
				$tempResult->setAdvertisementPlaceId($data[$i][0]->getAdvertisementPlaceId());
				$tempResult->setCreateTime($data[$i][0]->getCreateTime());
				$tempResult->setDescription($data[$i][0]->getDescription());
				$tempResult->setLanguage($data[$i][0]->getLanguage());
				$tempResult->setLastUpdate($data[$i][0]->getLastUpdate());
				$tempResult->setSortId($data[$i][0]->getSortId());
				$tempResult->setStatus($data[$i][0]->getStatus());
				$tempResult->setTitle($data[$i][0]->getTitle());
				$tempResult->setUrl($data[$i][0]->getUrl());


				//$tempResult->placeName = $data[$i][0]->placeName;


				$tempResult->placeName = $data[$i]["placeName"];

				$fileUrl = $data[$i][0]->getFileUrl();
				if (!empty($fileUrl)) {
					$fileUrl = $advertisementUrl . $fileUrl;
				}
				$tempResult->setFileUrl($fileUrl);

				array_push($dataResult, $tempResult);
			}

			$result = new PageResult();
			$result->data = $dataResult;
			$result->pageIndex = $pageIndex;
			$result->pageSize = $pageSize;
			$result->totalCount = $page->getCount();
			$result->totalPages = $page->getTotalPages();
			return $result;
		} catch (NoResultException $e) {
		}

		return null;

	}

	public function getDisplayAdvertisementByFlag($flag, $language = CommonDefine::CHINESE) {
		if (empty($flag)) {
			return null;
		}
		$flag = strtoupper($flag);
		$whereStr = "a.status = " . CommonDefine::DATA_STATUS_NORMAL;
		$paramArr = array();

		$whereStr1 = " and ap.status = " . CommonDefine::DATA_STATUS_NORMAL;
		$whereStr .= " and ap.flag=:flag";
		$paramArr["flag"] = $flag;

		$whereStr .= " and a.language=:language";
		$paramArr["language"] = $language;

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('a')
			->from('Clean\LibraryBundle\Entity\AdvertisementEntity', 'a')
			->leftJoin(
				'Clean\LibraryBundle\Entity\AdvertisementPlaceEntity',
				'ap',
				\Doctrine\ORM\Query\Expr\Join::WITH,
				'a.advertisementPlaceId = ap.advertisementPlaceId' . $whereStr1
			)
			->where($whereStr)
			->setParameters($paramArr)
			->addOrderBy('a.sortId', 'ASC')
			->addOrderBy("a.advertisementId", "DESC");

		$result = $query->getQuery()->getResult();

		if (!empty($result)) {
			$advertisementUrl = ConfigHandler::getCommonConfig("advertisementUrl");
			for ($i = 0; $i < count($result); $i++) {
				$fileUrl = $result[$i]->getFileUrl();
				if (!empty($fileUrl)) {
					$fileUrl = $advertisementUrl . $fileUrl;
				}
				$result[$i]->setFileUrl($fileUrl);
			}
		}

		return $result;

	}

	public function addAdvertisement(AdvertisementEntity $entity) {
		if (empty($entity)) {
			throw new \Exception("数据异常");
		}

		$entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
		$entity->setCreateTime(new \DateTime());
		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->persist($entity);
		$this->entityManager->flush($entity);
		return $entity->getAdvertisementId();
	}

	public function editAdvertisement(AdvertisementEntity $entity) {
		if (empty($entity)) {
			throw new \Exception("数据异常");
		}

		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->flush($entity);
	}

	public function deleteAdvertisement($advertisementId) {
		$entity = $this->getAdvertisement($advertisementId);

		if (empty($entity)) {
			throw new \Exception("数据异常");
		}

		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);
		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->flush($entity);

	}

}

?>