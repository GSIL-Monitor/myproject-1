<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="firmware_operation_record")
 */
class FirmwareOperationRecordEntity {
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $firmwareOperationRecordId;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $adminUserId;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $isAutoUpdate;

	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $isAllSn;

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
     * Get firmwareOperationRecordId
     *
     * @return integer
     */
    public function getFirmwareOperationRecordId()
    {
        return $this->firmwareOperationRecordId;
    }

    /**
     * Set adminUserId
     *
     * @param integer $adminUserId
     *
     * @return FirmwareOperationRecordEntity
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;

        return $this;
    }

    /**
     * Get adminUserId
     *
     * @return integer
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return FirmwareOperationRecordEntity
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isAutoUpdate
     *
     * @param integer $isAutoUpdate
     *
     * @return FirmwareOperationRecordEntity
     */
    public function setIsAutoUpdate($isAutoUpdate)
    {
        $this->isAutoUpdate = $isAutoUpdate;

        return $this;
    }

    /**
     * Get isAutoUpdate
     *
     * @return integer
     */
    public function getIsAutoUpdate()
    {
        return $this->isAutoUpdate;
    }

    /**
     * Set isAllSn
     *
     * @param integer $isAllSn
     *
     * @return FirmwareOperationRecordEntity
     */
    public function setIsAllSn($isAllSn)
    {
        $this->isAllSn = $isAllSn;

        return $this;
    }

    /**
     * Get isAllSn
     *
     * @return integer
     */
    public function getIsAllSn()
    {
        return $this->isAllSn;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return FirmwareOperationRecordEntity
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
     * @return FirmwareOperationRecordEntity
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
     * @return FirmwareOperationRecordEntity
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
