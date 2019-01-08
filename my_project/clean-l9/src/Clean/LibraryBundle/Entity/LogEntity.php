<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="log")
 */ 
class LogEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $logId;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $sn;
    
    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $time;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $event;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $workType;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $levelNumber;

     /**
     * @ORM\Column(type="string", length=100)
     */
    protected $location;

   /**
     * @ORM\Column(type="string", length=500)
     */
    protected $message;
    
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
     * Get logId
     *
     * @return integer
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * Set time
     *
     * @param string $time
     *
     * @return LogEntity
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set event
     *
     * @param string $event
     *
     * @return LogEntity
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }


     /**
     * Set workType
     *
     * @param string $workType
     *
     * @return LogEntity
     */
    public function setWorkType($workType)
    {
        $this->workType = $workType;

        return $this;
    }

    /**
     * Get workType
     *
     * @return string
     */
    public function getWorkType()
    {
        return $this->workType;
    }

    /**
     * Set levelNumber
     *
     * @param string $levelNumber
     *
     * @return LogEntity
     */
    public function setLevelNumber($levelNumber)
    {
        $this->levelNumber = $levelNumber;

        return $this;
    }

    /**
     * Get levelNumber
     *
     * @return string
     */
    public function getLevelNumber()
    {
        return $this->levelNumber;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return LogEntity
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return LogEntity
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return LogEntity
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
     * @return LogEntity
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
     * @return LogEntity
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
     * Set sn
     *
     * @param string $sn
     *
     * @return LogEntity
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

}
