<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="system_message")
 */
class SystemMessageEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $systemMessageId;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected  $title;

     /**
     * @ORM\Column(type="string", length=800)
     */
    protected  $messageContent;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $messageType;

    /**
     * @ORM\Column(type="integer")
     */
    protected $companyId;

     /**
     * @ORM\Column(type="integer")
     */
    protected $toUserId;

       /**
     * @ORM\Column(type="integer")
     */
    protected $fromUserId;
    
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
     * Get systemMessageId
     *
     * @return integer
     */
    public function getSystemMessageId()
    {
        return $this->systemMessageId;
    }

    /**
     * Set messageContent
     *
     * @param string $messageContent
     *
     * @return SystemMessageEntity
     */
    public function setMessageContent($messageContent)
    {
        $this->messageContent = $messageContent;

        return $this;
    }

    /**
     * Get messageContent
     *
     * @return string
     */
    public function getMessageContent()
    {
        return $this->messageContent;
    }

    /**
     * Set messageType
     *
     * @param integer $messageType
     *
     * @return SystemMessageEntity
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;

        return $this;
    }

    /**
     * Get messageType
     *
     * @return integer
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * Set companyId
     *
     * @param integer $companyId
     *
     * @return SystemMessageEntity
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

    /**
     * Set toUserId
     *
     * @param integer $toUserId
     *
     * @return SystemMessageEntity
     */
    public function setToUserId($toUserId)
    {
        $this->toUserId = $toUserId;

        return $this;
    }

    /**
     * Get toUserId
     *
     * @return integer
     */
    public function getToUserId()
    {
        return $this->toUserId;
    }

    /**
     * Set fromUserId
     *
     * @param integer $fromUserId
     *
     * @return SystemMessageEntity
     */
    public function setFromUserId($fromUserId)
    {
        $this->fromUserId = $fromUserId;

        return $this;
    }

    /**
     * Get fromUserId
     *
     * @return integer
     */
    public function getFromUserId()
    {
        return $this->fromUserId;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return SystemMessageEntity
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
     * @return SystemMessageEntity
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
     * @return SystemMessageEntity
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
     * Set title
     *
     * @param string $title
     *
     * @return SystemMessageEntity
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
