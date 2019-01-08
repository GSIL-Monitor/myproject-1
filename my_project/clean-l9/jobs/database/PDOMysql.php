<?php

class PDOMysql
{
    private $dbHost;
    private $dbUserName;
    private $dbPassword;
    private $dbDatabase;
    private $dbCharset;
    private $dbConnection;
    public function __construct($dbHost, $dbUserName, $dbPassword, $dbDatabase, $dbCharset="UTF8")
    {
        $this->dbHost = $dbHost;
        $this->dbUserName = $dbUserName;
        $this->dbPassword = $dbPassword;
        $this->dbDatabase = $dbDatabase;
        $this->dbCharset = $dbCharset;
        $this->connect();
    }
    
    private function connect()
    {
        $this->dbConnection = new PDO('mysql:host='.$this->dbHost.';dbname='.$this->dbDatabase.';charset='.$this->dbCharset
                , $this->dbUserName, $this->dbPassword);
        
        $this->dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    private function prepareSql($sql,$paramArr)
    {   
         $statement = $this->dbConnection->prepare($sql);
         $statement->execute($paramArr);
         return $statement;
    }
    
    private function getLastInsertId()
    {
    	return $this->dbConnection->lastInsertId();
    }
    
    public function fetchOne($sql,$paramArr)
    {
    	$statement=$this->prepareSql($sql, $paramArr);
    	$result  = $statement->fetch(PDO::FETCH_ASSOC);
    	$statement->closeCursor();
    	return $result;
    }
    
    public function fetchAll($sql,$paramArr)
    {
        $statement=$this->prepareSql($sql, $paramArr);
        $result  = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }
    
    public function insert($tableName,$paramArr)
    {
        if(!is_array($paramArr))
        {
        	return 0;
        }
        $columns="";
        $values="";
        foreach ($paramArr as $key=>$value)
        {
        	$columns.=$key.",";
        	$values.=":".$key.",";
        }
        $columns=trim($columns,',');
        $values=trim($values,',');
        $sql="insert into $tableName ($columns) values ($values)";
        $statement=$this->prepareSql($sql, $paramArr);
        $result=$this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }
    
    public function updateById($tableName, $paramArr,$idFieldName,$id)
    {
        if(!is_array($paramArr))
        {
            return 0;
        }

        $setStr="";
        foreach ($paramArr as $key=>$value)
        {
            $setStr .= $key."=:".$key.",";
        }
        $setStr=trim($setStr,',');
        
        $sql="update $tableName set $setStr where $idFieldName=$id";
        $statement=$this->prepareSql($sql, $paramArr);
        $result=$this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }
    
    public function updateByCondition($tableName, $paramArr,$whereStr)
    {
        if(!is_array($paramArr))
        {
            return 0;
        }
    
        $setStr="";
        foreach ($paramArr as $key=>$value)
        {
            $setStr .= $key."=:".$key.",";
        }
        $setStr=trim($setStr,',');
    
        $sql="update $tableName set $setStr where $whereStr";
        $statement=$this->prepareSql($sql, $paramArr);
        $result=$this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }
    
    public function deleteById($tableName,$idFieldName,$id)
    {
        if(!$idFieldName || !$id)
        {
        	return 0;
        }
        
        $sql="delete from $tableName where $idFieldName=$id";
        $statement=$this->prepareSql($sql, array());
        $result=$this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }
    
    public function excute($sql,$paramArr)
    {
        $statement=$this->prepareSql($sql, $paramArr);
        $statement->closeCursor();
    }

    public  function all($sql,$paramArr)
    {
        $statement=$this->prepareSql($sql, $paramArr);
        $result=$this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }
    
}



?>