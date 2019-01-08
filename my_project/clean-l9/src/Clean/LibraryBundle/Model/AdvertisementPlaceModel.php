<?php
namespace Clean\LibraryBundle\Model;
use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\AdvertisementPlaceEntity;

class AdvertisementPlaceModel extends BaseModel {
	private function getResponsity($entity = "CleanLibraryBundle:AdvertisementPlaceEntity") {
		return $this->entityManager->getRepository($entity);
	}

	public function getAdvertisementPlace($advertisementPlaceId) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"advertisementPlaceId" => $advertisementPlaceId,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;
	}

	public function getAdvertisementPlaceByFlag($flag) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"flag" => $flag,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;
	}

	public function getAdvertisementPlaceList() {
		$list = $this->getResponsity()->findBy(
			array(
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			),
			array(
				"advertisementPlaceId" => "DESC",
			)
		);
		return $list;
	}

	public function isExistAdvertisementPlaceByFlag($flag) {
		$result = $this->getAdvertisementPlaceByFlag($flag);
		return !empty($result);
	}

	public function addAdvertisementPlace(AdvertisementPlaceEntity $entity) {
		if (empty($entity)) {
			throw new \Exception("数据异常");
		}

		$flag = $entity->getFlag();
		if (!empty($flag)) {
			$entity->setFlag(strtoupper($flag));
		}

		$entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
		$entity->setCreateTime(new \DateTime());
		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->persist($entity);
		$this->entityManager->flush($entity);
		return $entity->getAdvertisementPlaceId();
	}

	public function editAdvertisementPlace(AdvertisementPlaceEntity $entity) {
		if (empty($entity)) {
			throw new \Exception("数据异常");
		}

		$flag = $entity->getFlag();
		if (!empty($flag)) {
			$entity->setFlag(strtoupper($flag));
		}

		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->flush($entity);
	}

	public function deleteAdvertisementPlace($advertisementPlaceId) {
		$entity = $this->getAdvertisementPlace($advertisementPlaceId);

		if (empty($entity)) {
			throw new \Exception("数据异常");
		}

		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);
		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->flush($entity);

	}

}

?>