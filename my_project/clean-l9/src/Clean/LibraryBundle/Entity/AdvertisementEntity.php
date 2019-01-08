<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="advertisement")
 */
class AdvertisementEntity {
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $advertisementId;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $title;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $fileUrl;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $url;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="string", length=10)
	 */
	protected $language;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $sortId;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $advertisementPlaceId;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $adminUserId;

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
	 * Get advertisementId
	 *
	 * @return integer
	 */
	public function getAdvertisementId() {
		return $this->advertisementId;
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 * @return AdvertisementEntity
	 */
	public function setTitle($title) {
		$this->title = $title;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set fileUrl
	 *
	 * @param string $fileUrl
	 * @return AdvertisementEntity
	 */
	public function setFileUrl($fileUrl) {
		$this->fileUrl = $fileUrl;

		return $this;
	}

	/**
	 * Get fileUrl
	 *
	 * @return string
	 */
	public function getFileUrl() {
		return $this->fileUrl;
	}

	/**
	 * Set url
	 *
	 * @param string $url
	 * @return AdvertisementEntity
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
	 * @return AdvertisementEntity
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
	 * Set sortId
	 *
	 * @param integer $sortId
	 * @return AdvertisementEntity
	 */
	public function setSortId($sortId) {
		$this->sortId = $sortId;

		return $this;
	}

	/**
	 * Get sortId
	 *
	 * @return integer
	 */
	public function getSortId() {
		return $this->sortId;
	}

	/**
	 * Set advertisementPlaceId
	 *
	 * @param integer $advertisementPlaceId
	 * @return AdvertisementEntity
	 */
	public function setAdvertisementPlaceId($advertisementPlaceId) {
		$this->advertisementPlaceId = $advertisementPlaceId;

		return $this;
	}

	/**
	 * Get advertisementPlaceId
	 *
	 * @return integer
	 */
	public function getAdvertisementPlaceId() {
		return $this->advertisementPlaceId;
	}

	/**
	 * Set adminUserId
	 *
	 * @param integer $adminUserId
	 * @return AdvertisementEntity
	 */
	public function setAdminUserId($adminUserId) {
		$this->adminUserId = $adminUserId;

		return $this;
	}

	/**
	 * Get adminUserId
	 *
	 * @return integer
	 */
	public function getAdminUserId() {
		return $this->adminUserId;
	}

	/**
	 * Set status
	 *
	 * @param integer $status
	 * @return AdvertisementEntity
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
	 * @return AdvertisementEntity
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
	 * @return AdvertisementEntity
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
	 * Set language
	 *
	 * @param string $language
	 * @return AdvertisementEntity
	 */
	public function setLanguage($language) {
		$this->language = $language;

		return $this;
	}

	/**
	 * Get language
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}
}
