<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="OQC")
 */
class OQCEntity {
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $OQCId;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $sn;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $companyId;

	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $status;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $createTime;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $lastUpdate;

	/**
	 * Get OQCId
	 *
	 * @return integer
	 */
	public function getOQCId() {
		return $this->OQCId;
	}

	/**
	 * Set companyId
	 *
	 * @param integer $companyId
	 * @return UserInfoEntity
	 */
	public function setCompanyId($companyId) {
		$this->companyId = $companyId;

		return $this;
	}

	/**
	 * Get companyId
	 *
	 * @return integer
	 */
	public function getCompanyId() {
		return $this->companyId;
	}

	/**
	 * Set sn
	 *
	 * @param string $sn
	 * @return UserInfoEntity
	 */
	public function setSn($sn) {
		$this->sn = $sn;

		return $this;
	}

	/**
	 * Get sn
	 *
	 * @return string
	 */
	public function getSn() {
		return $this->sn;
	}

	/**
	 * Set status
	 *
	 * @param integer $status
	 * @return UserInfoEntity
	 */
	public function setStatus($status) {
		$this->status = $status;

		return $this;
	}

	/**
	 * Get status
	 *
	 * @return integer
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Set createTime
	 *
	 * @param \DateTime $createTime
	 * @return UserInfoEntity
	 */
	public function setCreateTime($createTime) {
		$this->createTime = $createTime;

		return $this;
	}

	/**
	 * Get createTime
	 *
	 * @return \DateTime
	 */
	public function getCreateTime() {
		return $this->createTime;
	}

	/**
	 * Set lastUpdate
	 *
	 * @param \DateTime $lastUpdate
	 * @return UserInfoEntity
	 */
	public function setLastUpdate($lastUpdate) {
		$this->lastUpdate = $lastUpdate;

		return $this;
	}

	/**
	 * Get lastUpdate
	 *
	 * @return \DateTime
	 */
	public function getLastUpdate() {
		return $this->lastUpdate;
	}

}
