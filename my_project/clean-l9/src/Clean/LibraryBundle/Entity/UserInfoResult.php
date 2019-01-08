<?php
namespace Clean\LibraryBundle\Entity;

class UserInfoResult extends UserInfoEntity
{
	public function setUserId($userId)
	{
		$this->userId=$userId;
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

	private $unRead;
	public function setUnRead($unRead)
	{
		$this->unRead=$unRead;
	}
	public function getUnRead()
	{
		return $this->unRead;
	}

	private $machineName;
	public function setMachineName($machineName)
	{
		$this->machineName=$machineName;
	}
	public function getMachineName()
	{
		return $this->machineName;
	}

	private $isOnline;
	public function setIsOnline($isOnline)
	{
		$this->isOnline=$isOnline;
	}
	public function getIsOnline()
	{
		return $this->isOnline;
	}


}

?>