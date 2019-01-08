<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Model\BaseModelAbstract;

class WhiteGroupSnModel extends BaseModelAbstract {
	private function getResponsity($entity = "CleanLibraryBundle:WhiteGroupSnEntity") {
		return $this->entityManager->getRepository($entity);
	}
	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::getEntity()
	*/
	public function getEntity($id) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"whiteGroupSnId" => $id,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getEntityBySnAndWhiteGroupId($sn, $whiteGroupId) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"sn" => $sn,
				"whiteGroupId" => $whiteGroupId,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;
	}

	public function getEntityList() {
		$result = $this->getResponsity()->findBy(
			array(
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getSnByWhiteGroupId($whiteGroupId) {

		$whereStr = "wg.status = :status";
		$whereStr .= " and wg.whiteGroupId=:whiteGroupId";

		$paramArr = array(

			"whiteGroupId" => $whiteGroupId,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		);

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('wg.sn')
			->from('Clean\LibraryBundle\Entity\WhiteGroupSnEntity', 'wg')
			->setParameters($paramArr)
			->where($whereStr);

		$result = $query->getQuery()->getResult();
		return $result;

	}

	public function getEntityListByWhiteGroupIdAndSn($whiteGroupId, $sn) {
		$arr = array(
			"whiteGroupId" => $whiteGroupId,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		);
		if ($sn) {
			$arr["sn"] = $sn;
		}
		$result = $this->getResponsity()->findBy(
			$arr
		);
		return $result;
	}

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addEntity()
	*/
	public function addEntity($entity) {
		if (empty($entity)) {
			throw new Exception("数据异常");
		}
		$entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
		$entity->setCreateTime(new \DateTime());
		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->persist($entity);
		$this->entityManager->flush($entity);
		return $entity->getWhiteGroupSnId();

	}

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addBatchEntity()
	*/
	public function addBatchEntity($entityArr) {
		if (empty($entityArr)) {
			throw new Exception("数据异常");
		}

		$this->entityManager->clear();

		for ($i = 0; $i < count($entityArr); $i++) {
			$entityArr[$i]->setStatus(CommonDefine::DATA_STATUS_NORMAL);
			$entityArr[$i]->setCreateTime(new \DateTime());
			$entityArr[$i]->setLastUpdate(new \DateTime());

			$this->entityManager->merge($entityArr[$i]);
		}
		$this->entityManager->flush();

	}

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::editEntity()
	*/
	public function editEntity($entity) {
		if (empty($entity)) {
			throw new Exception("数据异常");
		}

		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->flush($entity);

	}

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::deleteEntity()
	*/
	public function deleteEntity($id) {
		$entity = $this->getEntity($id);

		if (empty($entity)) {
			throw new Exception("数据异常");
		}

		$entity->setLastUpdate(new \DateTime());
		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);

		$this->entityManager->flush($entity);

	}

}

?>