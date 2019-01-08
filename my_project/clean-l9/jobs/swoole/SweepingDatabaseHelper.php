<?php
require_once dirname(dirname(__FILE__)).'/database/PDOMysql.php';
require_once 'CommonDefine.php';

class SweepingDatabaseHelper
{
    private $dbObj;

    public function __construct()
    {
        $dbHost = "localhost";
        $dbUser = "root";
        $dbPwd = "My123@db";
        $dbDatabase = "clean";
        $dbCharset="utf8mb4";
        
        $this->dbObj = new PDOMysql($dbHost, $dbUser, $dbPwd, $dbDatabase,$dbCharset);
    }

    public function getUserInfo($userId)
    {
        $sql = "select ui.userId,ui.userName,ui.avatar from clean.user_info ui
              where ui.userId=:userId and ui.status=1
                ";
        $paramArr=array(
                "userId"=>$userId
        );
        $result = $this->dbObj->fetchOne($sql, $paramArr);
        if(!empty($result) && array_key_exists("avatar", $result))
        {
            $result["robotList"]=$this->getUserRobotListByUser($userId);
        }
        return $result;
    }

    public function getUserNowSn($userId)
    {
        $sql = "select ui.nowSn from clean.user_info ui
              where ui.userId=:userId and ui.status=1
                ";
        $paramArr=array(
                "userId"=>$userId
        );
        $result = $this->dbObj->fetchOne($sql, $paramArr);
        return $result;
    }
    
    public function getRobotInfo($sn)
    {
        $sql = "select sn,machineName from clean.machine ci
              where ci.sn=:sn and ci.status=1
                ";
        $paramArr=array(
                "sn"=>$sn
        );
        $result = $this->dbObj->fetchOne($sql, $paramArr);
        if(!empty($result))
        {
            $result["userList"]=$this->getUserRobotListBySN($sn);
        }
        return $result;
    }
    
    public function getUserRobotListByUser($userId)
    {
        $sql = "select sn,userId from clean.user_machine uc
              where uc.userId=:userId and uc.status=1
                ";
        $paramArr=array(
            "userId"=>$userId
        );
        $result = $this->dbObj->fetchAll($sql, $paramArr);
        return $result;
    }
    
    public function getUserRobotListBySN($sn)
    {
        $sql = "select um.sn,um.userId from clean.user_machine um
            left join clean.user_info ui
                on um.userId=ui.userId
              where um.sn=:sn  and um.status=1 and um.sn=ui.nowSn
                ";
        $paramArr=array(
                "sn"=>$sn
        );
        $result = $this->dbObj->fetchAll($sql, $paramArr);
        return $result;
    }
    
    public function addUserMachine($paramArr)
    {
        if(!is_array($paramArr))
        {
            return 0;
        }
    
        $paramArr["status"]=1;
        $paramArr["createTime"]=date("Y-m-d H:i:s");
        $paramArr["lastUpdate"]=date("Y-m-d H:i:s");
    
        $id = $this->dbObj->insert("clean.user_machine", $paramArr);
        return $id;
    }


    public function getUserMachinetBySNAndUserId($sn,$userId)
    {
        $sql = "select sn,userId from clean.user_machine um
              where um.sn=:sn and um.userId=:userId and um.status=1
                ";
        $paramArr=array(
                "sn"=>$sn,
                "userId"=>$userId
        );
        $result = $this->dbObj->fetchAll($sql, $paramArr);
        return $result;
    }

    public function updateUserMachine($sn,$userId)
    {   
        $paramArr=array(
                "userType"=>2,
                "sn"=>$sn,
                "userId"=>$userId
        );
        $sql = "update clean.user_machine um set um.userType=:userType where um.sn=:sn and um.userId != :userId";
        $result = $this->dbObj->all($sql,$paramArr);
        return $result;
    }

    public function updateUserInfo($sn,$userId)
    {   
        $paramArr=array(
                "sn"=>$sn,
                "userId"=>$userId
        );
        $sql = "update clean.user_info ui set ui.nowSn=:sn where  ui.userId = :userId";
        $result = $this->dbObj->all($sql,$paramArr);
        return $result;
    }

    public function getUserLoginToken($userId)
    {
        $sql = "select loginToken,aesKEY,aesIV from clean.login_token lt
              where lt.userId=:userId and lt.status=1
                ";
        $paramArr=array(
            "userId"=>$userId
        );
        $result = $this->dbObj->fetchOne($sql, $paramArr);
        return $result;
    }


    public function getRobotInfoBySn($sn)
    {
        $sql = "select machineDataId from clean.machine_data cmd
              where cmd.sn=:sn and cmd.status=1
                ";
        $paramArr=array(
                "sn"=>$sn
        );
        $result = $this->dbObj->fetchOne($sql, $paramArr);
        return $result;
    }

    public function updateMachineDataInfo($machineDataId,$dataArr)
    {   

        $paramArr=array(
                "time"=>$dataArr["time"],
                "mopArea"=>$dataArr["mopArea"],
                "sweepArea"=>$dataArr["sweepArea"],
                "counts"=>$dataArr["counts"]
        );
        $result = $this->dbObj->updateById("machine_data", $paramArr,"machineDataId",$machineDataId);
        return $result;
    }

    public function addMachineData($paramArr)
    {   
        if(!is_array($paramArr))
        {
            return 0;
        }
    
        $paramArr["status"]=1;
        $paramArr["createTime"]=date("Y-m-d H:i:s");
        $paramArr["lastUpdate"]=date("Y-m-d H:i:s");
    
        $id = $this->dbObj->insert("clean.machine_data", $paramArr);
        return $id;
    }
    
}

?>