<?php
namespace Clean\LibraryBundle\Entity;

class AdminUserResult extends AdminUserEntity
{
	public function setAdminUserId($adminUserId)
	{
		$this->adminUserId=$adminUserId;
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