<?php
namespace Clean\LibraryBundle\Entity;

class MachineResult extends MachineEntity
{
	public function setMachineId($machineId)
	{
		$this->machineId=$machineId;
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