<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="advertisement_place")
 */
class AdvertisementPlaceEntity {
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $advertisementPlaceId;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $placeName;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $flag;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $placeWidth;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $placeHeight;

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
	 * Get advertisementPlaceId
	 *
	 * @return integer
	 */
	public function getAdvertisementPlaceId() {
		return $this->advertisementPlaceId;
	}

	/**
	 * Set placeName
	 *
	 * @param string $placeName
	 * @return AdvertisementPlaceEntity
	 */
	public function setPlaceName($placeName) {
		$this->placeName = $placeName;

		return $this;
	}

	/**
	 * Get placeName
	 *
	 * @return string
	 */
	public function getPlaceName() {
		return $this->placeName;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 * @return AdvertisementPlaceEntity
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
	 * Set flag
	 *
	 * @param string $flag
	 * @return AdvertisementPlaceEntity
	 */
	public function setFlag($flag) {
		$this->flag = $flag;

		return $this;
	}

	/**
	 * Get flag
	 *
	 * @return string
	 */
	public function getFlag() {
		return $this->flag;
	}

	/**
	 * Set adminUserId
	 *
	 * @param integer $adminUserId
	 * @return AdvertisementPlaceEntity
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
	 * @return AdvertisementPlaceEntity
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
	 * @return AdvertisementPlaceEntity
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
	 * @return AdvertisementPlaceEntity
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
	 * Set placeWidth
	 *
	 * @param integer $placeWidth
	 * @return AdvertisementPlaceEntity
	 */
	public function setPlaceWidth($placeWidth) {
		$this->placeWidth = $placeWidth;

		return $this;
	}

	/**
	 * Get placeWidth
	 *
	 * @return integer
	 */
	public function getPlaceWidth() {
		return $this->placeWidth;
	}

	/**
	 * Set placeHeight
	 *
	 * @param integer $placeHeight
	 * @return AdvertisementPlaceEntity
	 */
	public function setPlaceHeight($placeHeight) {
		$this->placeHeight = $placeHeight;

		return $this;
	}

	/**
	 * Get placeHeight
	 *
	 * @return integer
	 */
	public function getPlaceHeight() {
		return $this->placeHeight;
	}
}
