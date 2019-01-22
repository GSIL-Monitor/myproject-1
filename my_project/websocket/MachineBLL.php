<?php
require_once 'SweepingDatabaseHelper.php';
require_once 'CommonDefine.php';

class MachineBLL
{
  
    public  function  updateMachineCleanData($fd,$dataArr)
    {   

        $sn = $this->getConnectorId($fd);
        if($sn && strlen($sn)==16)
        {   
            $dbObj=new SweepingDatabaseHelper();
            $machineDataInfo = $dbObj->getRobotInfoBySn($sn);
            if(!$machineDataInfo)
            {
                //添加
                $paramArr = array(
                        "sn"=>$sn,
                        "time"=>$dataArr["time"],
                        "mopArea"=>$dataArr["mopArea"],
                        "sweepArea"=>$dataArr["sweepArea"],
                        "counts"=>$dataArr["counts"]
                    );
                $dbObj->addMachineData($paramArr);

            }else
            {
                //更新
                $machineDataId = $machineDataInfo["machineDataId"];
                $dbObj->updateMachineDataInfo($machineDataId,$dataArr);

            }

            return true;

        }
        
        return false;
    }

    /*
     * 获取该连接的用户Id
    */
    private  function getConnectorId($fd)
    {
        $connector=$this->getConnector($fd);
        if(!empty($connector))
        {
            if(array_key_exists("sn", $connector))
            {   
                if( strlen($connector["sn"]) < 16)
                {
                    return false;
                }
                return $connector["sn"];
            }
        }
        return 0;
    }

     /*
     * 获取用户连接
    */
    private function getConnector($fd)
    {   
        $fd=intval($fd);
        $subDir=substr(strval($fd), 0,1);
        $filename=CommonDefine::CONNECTOR_DIR."fd/".$subDir."/".$fd;
        if(!file_exists($filename))
        {   
            return null;
        }
        $content=file_get_contents($filename);
        return json_decode($content,true);
    }

    
    
    
    
    
    
}

?>