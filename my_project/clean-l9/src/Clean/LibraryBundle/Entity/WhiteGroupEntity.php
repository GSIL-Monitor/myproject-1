<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="white_group")
 */
class WhiteGroupEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $whiteGroupId;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $groupName;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $sortId;
    
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
     * Get whiteGroupId
     *
     * @return integer
     */
    public function getWhiteGroupId()
    {
        return $this->whiteGroupId;
    }

    /**
     * Set groupName
     *
     * @param string $groupName
     *
     * @return WhiteGroupEntity
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

    /**
     * Get groupName
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * Set sortId
     *
     * @param integer $sortId
     *
     * @return WhiteGroupEntity
     */
    public function setSortId($sortId)
    {
        $this->sortId = $sortId;

        return $this;
    }

    /**
     * Get sortId
     *
     * @return integer
     */
    public function getSortId()
    {
        return $this->sortId;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return WhiteGroupEntity
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
     * @return WhiteGroupEntity
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
     * @return WhiteGroupEntity
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
