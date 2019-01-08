<?php
namespace Clean\LibraryBundle\Model;

use Doctrine\ORM\EntityManager;


abstract  class BaseModelAbstract
{
	
	protected  $entityManager;
    public function __construct(EntityManager $entityManager)
    {
       $this->entityManager = $entityManager;
    }
    
    abstract public function getEntity($id);
    abstract public function addEntity($entity);
    abstract public function addBatchEntity($entityArr);
    abstract public function editEntity($entity);
    abstract public function deleteEntity($id);
}

?>