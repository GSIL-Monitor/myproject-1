<?php
namespace Clean\LibraryBundle\Entity;

class ErrorCodeResult extends ErrorCodeEntity
{
	public function setErrorCodeId($errorCodeId)
	{
		$this->errorCodeId=$errorCodeId;
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