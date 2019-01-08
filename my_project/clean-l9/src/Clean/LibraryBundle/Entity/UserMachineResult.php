<?php
namespace Clean\LibraryBundle\Entity;

class UserMachineResult extends UserMachineEntity
{
	public function setUserMachineId($userMachineId)
	{
		$this->userMachineId=$userMachineId;
	}
	
	private $machineName;
	public function setmachineName($machineName)
	{
		$this->machineName=$machineName;
	}
	public function getmachineName()
	{
		return $this->machineName;
	}

	private $userName;
	public function setUserName($userName)
	{
		$this->userName=$userName;
	}
	public function getUserName()
	{
		return $this->userName;
	}


	private $avatar;
	public function setAvatar($avatar)
	{
		$this->avatar=$avatar;
	}
	public function getAvatar()
	{
		return $this->avatar;
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

	private $nickName;
	public function setNickName($nickName)
	{
		$this->nickName=$nickName;
	}
	public function getNickName()
	{
		return $this->nickName;
	}


}

?>