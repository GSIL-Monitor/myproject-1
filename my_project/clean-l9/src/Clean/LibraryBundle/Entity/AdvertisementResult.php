<?php
namespace Clean\LibraryBundle\Entity;

class AdvertisementResult extends AdvertisementEntity {
	public function setAdvertisementId($advertisementId) {
		$this->advertisementId = $advertisementId;
	}

	public $placeName;
}
?>