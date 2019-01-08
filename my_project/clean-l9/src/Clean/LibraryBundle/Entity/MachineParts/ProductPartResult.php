<?php
namespace Clean\LibraryBundle\Entity\MachineParts;

class ProductPartResult extends ProductPartEntity
{
    public function setProductPartId($productPartId)
    {
        $this->productPartId=$productPartId;
    }
    
    private $typeName;
    public function setTypeName($typeName)
    {
        $this->typeName=$typeName;
    }
    public function getTypeName()
    {
        return $this->typeName;
    }

}

?>