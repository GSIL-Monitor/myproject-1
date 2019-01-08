<?php
namespace Clean\LibraryBundle\Model;

use Doctrine\ORM\EntityManager;


class BaseModel
{
	
	protected  $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}

?>