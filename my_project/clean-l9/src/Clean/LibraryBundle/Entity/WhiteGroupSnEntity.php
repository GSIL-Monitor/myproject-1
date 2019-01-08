<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="white_group_sn")
 */
class WhiteGroupSnEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $whiteGroupSnId;

    /**
     * @ORM\Column(type="integer")
     */
    protected $whiteGroupId;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $sn;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $noteName;
    
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
     * Get whiteGroupSnId
     *
     * @return integer
     */
    public function getWhiteGroupSnId()
    {
        return $this->whiteGroupSnId;
    }

    /**
     * Set sn
     *
     * @param string $sn
     *
     * @return WhiteGroupSnEntity
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
     * Set noteName
     *
     * @param string $noteName
     *
     * @return WhiteGroupSnEntity
     */
    public function setNoteName($noteName)
    {
        $this->noteName = $noteName;

        return $this;
    }

    /**
     * Get noteName
     *
     * @return string
     */
    public function getNoteName()
    {
        return $this->noteName;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return WhiteGroupSnEntity
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
     * @return WhiteGroupSnEntity
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
     * @return WhiteGroupSnEntity
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
     * Set whiteGroupId
     *
     * @param integer $whiteGroupId
     *
     * @return WhiteGroupSnEntity
     */
    public function setWhiteGroupId($whiteGroupId)
    {
        $this->whiteGroupId = $whiteGroupId;

        return $this;
    }

    /**
     * Get whiteGroupId
     *
     * @return integer
     */
    public function getWhiteGroupId()
    {
        return $this->whiteGroupId;
    }
}
