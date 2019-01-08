<?php
namespace Clean\LibraryBundle\Entity;

class BasicInfoResult extends BasicInfoEntity
{
	public function setBasicInfoId($basicInfoId)
	{
		$this->basicInfoId=$basicInfoId;
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

}

?>