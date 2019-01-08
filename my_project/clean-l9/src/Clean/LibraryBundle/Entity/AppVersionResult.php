<?php
namespace Clean\LibraryBundle\Entity;

class AppVersionResult extends AppVersionEntity
{
	public function setAppVersionId($appVersionId)
	{
		$this->appVersionId=$appVersionId;
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