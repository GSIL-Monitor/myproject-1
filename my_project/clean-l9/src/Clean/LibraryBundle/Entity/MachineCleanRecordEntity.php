<?php
/**
 * Created by PhpStorm.
 * User: AZ
 * Date: 2018/12/28
 * Time: 15:28
 */

namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="machine_clean_record")
 */
class MachineCleanRecordEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $MachineCleanRecordId;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $sn;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $url;

    /**
     * @ORM\Column(type="integer")
     */
    protected $cleanArea;

    /**
     * @ORM\Column(type="integer")
     */
    protected $mopArea;

    /**
     * @ORM\Column(type="integer")
     */
    protected $startTime;

    /**
     * @ORM\Column(type="integer")
     */
    protected $endTime;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sort;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createTime;

    /**
     * Get MachineCleanRecord
     *
     * @return integer
     */
    public function getMachineCleanRecordId() {
        return $this->MachineCleanRecordId;
    }

    /**
     * Set sn
     *
     * @param string $sn
     * @return MachineCleanRecordEntity
     */
    public function setSn($sn) {
        $this->sn = $sn;
        return $this;
    }

    /**
     * Get sn
     *
     * @return string
     */
    public function getSn() {
        return $this->sn;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return MachineCleanRecordEntity
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set cleanArea
     *
     * @param integer cleanArea
     * @return MachineCleanRecordEntity
     */
    public function setCleanArea($cleanArea) {
        $this->cleanArea = $cleanArea;

        return $this;
    }

    /**
     * Get cleanArea
     *
     * @return integer
     */
    public function getCleanArea() {
        return $this->cleanArea;
    }

    /**
     * Set mopArea
     *
     * @param integer mopArea
     * @return MachineCleanRecordEntity
     */
    public function setMopArea($mopArea) {
        $this->mopArea = $mopArea;

        return $this;
    }

    /**
     * Get mopArea
     *
     * @return integer
     */
    public function getMopArea() {
        return $this->mopArea;
    }

    /**
     * Set startTime
     *
     * @param integer startTime
     * @return MachineCleanRecordEntity
     */
    public function setStartTime($startTime) {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return integer
     */
    public function getStartTime() {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param integer endTime
     * @return MachineCleanRecordEntity
     */
    public function setEndTime($endTime) {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return integer
     */
    public function getEndTime() {
        return $this->endTime;
    }

    /**
     * Set status
     *
     * @param integer status
     * @return MachineCleanRecordEntity
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer status
     * @return MachineCleanRecordEntity
     */
    public function setSort($sort) {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getSort() {
        return $this->sort;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     *
     * @return MachineCleanRecordEntity
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
}