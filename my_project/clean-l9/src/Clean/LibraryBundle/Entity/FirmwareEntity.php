<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="firmware")
 */
class FirmwareEntity {
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $firmwareId;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $versionCode;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $firmwareName;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $url;

	/**
	 * @ORM\Column(type="string", length=500)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="string", length=500)
	 */
	protected $enDescription;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $checkCode;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $companyId;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $intVersionCode;

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
	 * @ORM\Column(type="smallint")
	 */
	protected $isAutoUpdate;

	/**
	 * @ORM\Column(type="string", length=500)
	 */
	protected $sns;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $displayVersionCode;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $whiteGroupIds;

	/**
	 * Get firmwareId
	 *
	 * @return integer
	 */
	public function getFirmwareId() {
		return $this->firmwareId;
	}

	/**
	 * Set versionCode
	 *
	 * @param string $versionCode
	 *
	 * @return FirmwareEntity
	 */
	public function setVersionCode($versionCode) {
		$this->versionCode = $versionCode;

		return $this;
	}

	/**
	 * Get versionCode
	 *
	 * @return string
	 */
	public function getVersionCode() {
		return $this->versionCode;
	}

	/**
	 * Set firmwareName
	 *
	 * @param string $firmwareName
	 *
	 * @return FirmwareEntity
	 */
	public function setFirmwareName($firmwareName) {
		$this->firmwareName = $firmwareName;

		return $this;
	}

	/**
	 * Get firmwareName
	 *
	 * @return string
	 */
	public function getFirmwareName() {
		return $this->firmwareName;
	}

	/**
	 * Set url
	 *
	 * @param string $url
	 *
	 * @return FirmwareEntity
	 */
	public function setUrl($url) {
		$this->url = $url;

		return $this;
	}

	/**
	 * Get url
	 *
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return FirmwareEntity
	 */
	public function setDescription($description) {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Set enDescription
	 *
	 * @param string $enDescription
	 *
	 * @return FirmwareEntity
	 */
	public function setEnDescription($enDescription) {
		$this->enDescription = $enDescription;

		return $this;
	}

	/**
	 * Get enDescription
	 *
	 * @return string
	 */
	public function getEnDescription() {
		return $this->enDescription;
	}

	/**
	 * Set companyId
	 *
	 * @param integer $companyId
	 *
	 * @return FirmwareEntity
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
	 * Set intVersionCode
	 *
	 * @param integer $intVersionCode
	 *
	 * @return FirmwareEntity
	 */
	public function setIntVersionCode($intVersionCode) {
		$this->intVersionCode = $intVersionCode;

		return $this;
	}

	/**
	 * Get intVersionCode
	 *
	 * @return integer
	 */
	public function getIntVersionCode() {
		return $this->intVersionCode;
	}

	/**
	 * Set status
	 *
	 * @param integer $status
	 *
	 * @return FirmwareEntity
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
	 *
	 * @return FirmwareEntity
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
	 *
	 * @return FirmwareEntity
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

	/**
	 * Set checkCode
	 *
	 * @param string $checkCode
	 *
	 * @return FirmwareEntity
	 */
	public function setCheckCode($checkCode) {
		$this->checkCode = $checkCode;

		return $this;
	}

	/**
	 * Get checkCode
	 *
	 * @return string
	 */
	public function getCheckCode() {
		return $this->checkCode;
	}

	/**
	 * Set isAutoUpdate
	 *
	 * @param integer $isAutoUpdate
	 *
	 * @return FirmwareEntity
	 */
	public function setIsAutoUpdate($isAutoUpdate) {
		$this->isAutoUpdate = $isAutoUpdate;

		return $this;
	}

	/**
	 * Get isAutoUpdate
	 *
	 * @return integer
	 */
	public function getIsAutoUpdate() {
		return $this->isAutoUpdate;
	}

	/**
	 * Set displayVersionCode
	 *
	 * @param string $displayVersionCode
	 *
	 * @return FirmwareEntity
	 */
	public function setDisplayVersionCode($displayVersionCode) {
		$this->displayVersionCode = $displayVersionCode;

		return $this;
	}

	/**
	 * Get displayVersionCode
	 *
	 * @return string
	 */
	public function getDisplayVersionCode() {
		return $this->displayVersionCode;
	}

	/**
	 * Set sns
	 *
	 * @param string $sns
	 *
	 * @return FirmwareEntity
	 */
	public function setSns($sns) {
		$this->sns = $sns;

		return $this;
	}

	/**
	 * Get sns
	 *
	 * @return string
	 */
	public function getSns() {
		return $this->sns;
	}

	/**
	 * Set whiteGroupIds
	 *
	 * @param string $whiteGroupIds
	 *
	 * @return FirmwareEntity
	 */
	public function setWhiteGroupIds($whiteGroupIds) {
		$this->whiteGroupIds = $whiteGroupIds;

		return $this;
	}

	/**
	 * Get whiteGroupIds
	 *
	 * @return string
	 */
	public function getWhiteGroupIds() {
		return $this->whiteGroupIds;
	}
}
