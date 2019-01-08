<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="machine")
 */
class MachineEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $machineId;
    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $machineName;
    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $sn;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $version;

      /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $hardware;
    

    /**
     * @ORM\Column(type="integer")
     */
    protected $companyId;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $machineType;


    /**
     * @ORM\Column(type="smallint")
     */
    protected $status;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createTime;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastUpdate;

    /**
     * Get machineId
     *
     * @return integer 
     */
    public function getMachineId()
    {
        return $this->machineId;
    }

    /**
     * Set machineName
     *
     * @param string $machineName
     * @return UserInfoEntity
     */
    public function setMachineName($machineName)
    {
        $this->machineName = $machineName;
    
        return $this;
    }

    /**
     * Get machineName
     *
     * @return string 
     */
    public function getMachineName()
    {
        return $this->machineName;
    }

    /**
     * Set sn
     *
     * @param string $sn
     * @return UserInfoEntity
     */
    public function setSn($sn)
    {
        $this->sn = $sn;
    
        return $this;
    }

    /**
     * Get sn
     *
     * @return string 
     */
    public function getSn()
    {
        return $this->sn;
    }


     /**
     * Set version
     *
     * @param string $version
     * @return UserInfoEntity
     */
    public function setVersion($version)
    {
        $this->version = $version;
    
        return $this;
    }

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }

     /**
     * Set hardware
     *
     * @param string $hardware
     * @return UserInfoEntity
     */
    public function setHardware($hardware)
    {
        $this->hardware = $hardware;
    
        return $this;
    }

    /**
     * Get hardware
     *
     * @return string 
     */
    public function getHardware()
    {
        return $this->hardware;
    }

    /**
     * Set machineType
     *
     * @param integer $machineType
     * @return UserInfoEntity
     */
    public function setMachineType($machineType)
    {
        $this->machineType = $machineType;
    
        return $this;
    }

    /**
     * Get machineType
     *
     * @return integer 
     */
    public function getMachineType()
    {
        return $this->machineType;
    }


    /**
     * Set status
     *
     * @param integer $status
     * @return UserInfoEntity
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return UserInfoEntity
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    
        return $this;
    }

    /**
     * Get createTime
     *
     * @return \DateTime 
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return UserInfoEntity
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    
        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }


    /**
     * Set companyId
     *
     * @param integer $companyId
     * @return UserInfoEntity
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    
        return $this;
    }

    /**
     * Get companyId
     *
     * @return integer 
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    
    
 

    
}
