<?php
/**
 * Created by PhpStorm.
 * User: AZ
 * Date: 2018/12/28
 * Time: 16:05
 */

namespace Clean\LibraryBundle\Model;


use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Model\Common\Paginator;

class MachineCleanRecordModel extends BaseModelAbstract
{
    private function getResponsity($entity = "CleanLibraryBundle:MachineCleanRecordEntity") {
        return $this->entityManager->getRepository($entity);
    }

    public function getEntity($id) {
        $result = $this->getResponsity()->findOneBy(
            array(
                "machineCleanRecordId" => $id,
                "status" => CommonDefine::DATA_STATUS_NORMAL,
            )
        );
        return $result;
    }

    public function getEntityBySn($sn) {
        $result = $this->getResponsity()->findOneBy(
            array(
                "sn" => $sn,
                "status" => CommonDefine::DATA_STATUS_NORMAL,
            )
        );
        return $result;
    }

    public function getEntityBySnAndSort($sn, $sort) {
        $result = $this->getResponsity()->findOneBy(
            array(
                "sn" => $sn,
                "sort" => $sort,
                "status" => CommonDefine::DATA_STATUS_NORMAL,
            )
        );
        return $result;
    }

    public function getPageSn($pageIndex, $pageSize, $sn) {

        $whereStr = "m.status = :status";
        $paramArr = array(
            "status" => CommonDefine::DATA_STATUS_NORMAL,
        );
        $query = $this->entityManager->createQueryBuilder();
        $whereStr .= " and m.sn=:sn ";
        $paramArr["sn"] = $sn;
        $query
            ->select('m')
            ->from('Clean\LibraryBundle\Entity\MachineCleanRecordEntity', 'm')
            ->setParameters($paramArr)
            ->where($whereStr)
            ->orderBy('m.startTime');
        $page = new Paginator();
        $data = $page->paginate($query, $pageIndex, $pageSize);
        $result = [];
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $result[$k]['sn'] = $v->getSn();
                $result[$k]['url'] = $v->getUrl();
                $result[$k]['starttime'] = $v->getStartTime();
                $result[$k]['endtime'] = $v->getEndTime();
                $result[$k]['cleanarea'] = $v->getCleanArea();
                $result[$k]['moparea'] = $v->getMopArea();
            }
        }
        return $result;
    }

    public function addEntity($entity)
    {
        // TODO: Implement addEntity() method.
        if (empty($entity)) {
            throw new Exception("数据异常");
        }
        $entity->setSn($entity->getSn());
        $entity->setUrl($entity->getUrl());
        $entity->setCleanArea($entity->getCleanArea());
        $entity->setMopArea($entity->getMopArea());
        $entity->setStartTime($entity->getStartTime());
        $entity->setEndTime($entity->getEndTime());
        $entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);

        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
        return $entity->getMachineCleanRecordId();
    }

    public function addBatchEntity($entityArr)
    {
        // TODO: Implement addBatchEntity() method.
        if (empty($entityArr)) {
            throw new Exception("数据异常");
        }
        $this->entityManager->clear();
        for ($i = 0; $i < count($entityArr); $i++) {
            $entityArr[$i]->setStatus(CommonDefine::DATA_STATUS_NORMAL);
            $this->entityManager->merge($entityArr[$i]);
        }
        $this->entityManager->flush();
    }

    public function editEntity($entity)
    {
        // TODO: Implement editEntity() method.
        if (empty($entity)) {
            throw new Exception("数据异常");
        }
        $this->entityManager->flush($entity);
    }

    public function deleteEntity($id)
    {
        // TODO: Implement deleteEntity() method.
        $entity = $this->getEntity($id);
        if (empty($entity)) {
            throw new Exception("数据异常");
        }
        $entity->setStatus(CommonDefine::DATA_STATUS_DELETE);
        $this->entityManager->flush($entity);
    }
}