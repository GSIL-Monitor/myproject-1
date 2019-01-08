<?php
namespace Clean\LibraryBundle\Entity;

class MachineMapResult extends MachineMapEntity
{
	public function setMachineMapId($machineMapId)
	{
		$this->machineMapId=$machineMapId;
	}
	
	public $sweeping;

}

?>