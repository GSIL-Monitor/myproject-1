<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="machine_data")
 */
class MachineDataEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $machineDataId;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $time;
 
    /**
     * @ORM\Column(type="integer")
     */
    protected $mopArea;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sweepArea;

    /**
     * @ORM\Column(type="integer")
     */
    protected $counts;
    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $sn;


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
     * Get machineDataId
     *
     * @return integer
     */
    public function getMachineDataId()
    {
        return $this->machineDataId;
    }

    /**
     * Set time
     *
     * @param integer $time
     *
     * @return MachineDataEntity
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set mopArea
     *
     * @param integer $mopArea
     *
     * @return MachineDataEntity
     */
    public function setMopArea($mopArea)
    {
        $this->mopArea = $mopArea;

        return $this;
    }

    /**
     * Get mopArea
     *
     * @return integer
     */
    public function getMopArea()
    {
        return $this->mopArea;
    }

    /**
     * Set sweepArea
     *
     * @param integer $sweepArea
     *
     * @return MachineDataEntity
     */
    public function setSweepArea($sweepArea)
    {
        $this->sweepArea = $sweepArea;

        return $this;
    }

    /**
     * Get sweepArea
     *
     * @return integer
     */
    public function getSweepArea()
    {
        return $this->sweepArea;
    }

    /**
     * Set counts
     *
     * @param integer $counts
     *
     * @return MachineDataEntity
     */
    public function setCounts($counts)
    {
        $this->counts = $counts;

        return $this;
    }

    /**
     * Get counts
     *
     * @return integer
     */
    public function getCounts()
    {
        return $this->counts;
    }

    /**
     * Set sn
     *
     * @param string $sn
     *
     * @return MachineDataEntity
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
     * Set status
     *
     * @param integer $status
     *
     * @return MachineDataEntity
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
     *
     * @return MachineDataEntity
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
     *
     * @return MachineDataEntity
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
}
