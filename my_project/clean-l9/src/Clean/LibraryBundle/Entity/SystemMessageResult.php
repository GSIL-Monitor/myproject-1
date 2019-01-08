<?php
namespace Clean\LibraryBundle\Entity;

class SystemMessageResult extends SystemMessageEntity
{
	public function setSystemMessageId($systemMessageId)
	{
		$this->systemMessageId=$systemMessageId;
	}
	
	private $isRead;
	public function setIsRead($isRead)
	{
		$this->isRead=$isRead;
	}
	public function getIsRead()
	{
		return $this->isRead;
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

	private $userName;
	public function setUserName($userName)
	{
		$this->userName=$userName;
	}
	public function getUserName()
	{
		return $this->userName;
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