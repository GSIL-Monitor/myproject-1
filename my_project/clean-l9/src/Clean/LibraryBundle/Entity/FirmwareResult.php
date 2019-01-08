<?php
namespace Clean\LibraryBundle\Entity;

class FirmwareResult extends FirmwareEntity
{
	public function setFirmwareId($firmwareId)
	{
		$this->firmwareId=$firmwareId;
	}
	
	private $companyName;
	public function setCompanyName($companyName)
	{
		$this->companyName=$companyName;
	}
	public function getCompanyName()
	{
		return $this->companyName;
	}

	public  $autoUpdate;

	public $nowVersionCode;

}

?>