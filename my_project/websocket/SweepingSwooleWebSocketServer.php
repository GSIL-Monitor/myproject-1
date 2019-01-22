<?php
/**
 * Created by PhpStorm.
 * User: AZ
 * Date: 2019/1/21
 * Time: 14:42
 * websocket 服务
 */

require "./server/websocket.php";

class SweepingSwooleWebSocketServer extends WebSocket
{
    public $userbll;
    public $checkbll;
    public $machinebll;

    public function __construct()
    {
        parent::__construct();
        require_once 'CommonDefine.php';
        require_once 'UserBLL.php';
        require_once 'MachineBLL.php';
        require_once 'CheckBll.php';
        $this->userbll = new UserBLL();
        $this->machinebll = new MachineBLL();
        $this->checkbll = new CheckBll();
    }

    /**
     * websocket 主进程开始
     * @param unknown $serv
     */
    public function onStart($serv) {
        global $argv;
        swoole_set_process_name("{$argv[0]} {$this->host}:{$this->port} master");
        echo "Start\n";

        /******************
         * ################
         * redis subscribe
         *****************/
        ini_set('default_socket_timeout', -1);
        $redis = new Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $redis->subscribe(['broadcast'], function ($redis, $chan, $msg) use ($serv){
            $this->redisSend($serv, $msg);
        });
    }

    /**
     * 客户端与服务器建立连接时调用
     */
    public function onOpen($serv, $req)
    {
        echo "Client {$req->fd} connect\n";
        $this->userbll->saveConnector($req->fd);
    }

    /**
     * 接收到客户端消息时调用
     */
    public function onMessage($serv, $fd, $data)
    {
        echo "Get Message From Client {$fd}\n";

        // send a task to task worker.
        $param = array(
            'fd' => $fd,
            'data' => base64_encode($data),
        );
        $serv->task(json_encode($param));
        echo "Continue Handle Worker\n";
    }

    /**
     * worker进程投递的任务在task_worker中完成
     * @param $serv
     * @param $task_id
     * @param $data
     */
    public function onFinish($serv, $task_id, $data) {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";
    }

    /**
     * 关闭一个连接时触发
     * 清除相应的redis
     */
    public function onClose($serv, $fd, $from_id)
    {
        $tem = "时间为" . date("Y-m-d H:i:s", time()) . "fd:" . $fd . "  reactorId:" . $from_id;
        $this->writeLog($tem, "close/messageClose");

        //通知用户端，sn已经断线
        //$this->messageClose($serv,$fd);
        $this->clearCacheData($serv, $fd);

        //清理
        $this->userbll->removeConnector($fd);
        echo "Client {$fd} close connection\n";
    }

    /**
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return bool|void
     * 任务队列
     */
    public function onTask($serv, $task_id, $from_id, $param) {
        echo "This Task {$task_id} from Worker {$from_id}\n";
        $paramArr = json_decode($param, true);
        $fd = $paramArr['fd'];
        $data = base64_decode($paramArr['data']);
        $this->send($serv, $fd, $data);
        return "Task {$task_id}'s result";
    }



    /**
     * @param $serv
     * @param $interval
     * 定时器
     */
    public function onTimer($serv, $interval)
    {
        $startFd = $this->userbll->getLastCheckFd();
        while (true) {
            $conn_list = $serv->connection_list($startFd, 100);
            if ($conn_list === false or count($conn_list) === 0) {
                $this->userbll->writeLastCheckFd(0);
                echo "finish\n";
                break;
            }
            $startFd = end($conn_list);
            echo "\n";
            foreach ($conn_list as $fd) {
                if ($this->userbll->isConnectorTimeOut($fd)) {
                    $tem = "时间为" . date("Y-m-d H:i:s", time()) . "fd:" . $fd;
                    $this->writeLog($tem, "close/onTimer");
                    $serv->close($fd);
                }
            }
        }
    }

    /**
     * 工作组开始
     * @param unknown $serv
     * @param unknown $worker_id
     */
    public function onWorkerStart($serv, $worker_id)
    {
        $this->processRename($serv, $worker_id);
        // 只有当worker_id为0时才添加定时器,避免重复添加
        if ($worker_id == 0) {
            //$this->userBLL->clearData();
            echo "clear data finished\n";
            //初始化检查fd为0
            $this->userbll->writeLastCheckFd(0);
            echo "init fd finished\n";
            //$serv->addtimer(5000);
            if (!$serv->taskworker) {
                $serv->tick(5000, function ($id) {
                    $this->tickerEvent($this->serv);
                });
            } else {
                $serv->addtimer(5000);
            }
            echo "start timer finished\n";
        }
    }

    public function createDataCacheTable() {
        $this->dataCacheTable = new swoole_table(1024);
        $this->dataCacheTable->column('fd', swoole_table::TYPE_INT, 8); //1,2,4,8
        $this->dataCacheTable->column('content', swoole_table::TYPE_STRING, 500000);
        $this->dataCacheTable->create();
    }

    private function send($serv, $fd, $data) {
        if (!empty($data)) {

            //websocket握手，如果是握手则直接返回
            if ($this->wsHandShake($serv, $fd, $data)) {
                echo "websocket handsake.\n";
                $this->userbll->saveConnector($fd, CommonDefine::CONNECTION_TYPE_WEBSOCKET);
                return;
            }

            //判断客户端类型，对websocket的消息进行解包
            $connectionType = $this->userbll->getConnectionType($fd);
            if ($connectionType == CommonDefine::CONNECTION_TYPE_WEBSOCKET) {
                echo "I am websocket.\n";
                $data = $this->unwrap($data);
            }
            //数据拆包
            //echo $data."000000000000\n";

            $messageArr = $this->getSplitDataList($serv, $fd, $data);

            if (empty($messageArr) && !is_array($messageArr)) {
                return;
            }
            for ($i = 0; $i < count($messageArr); $i++) {
                $this->sendMessage($serv, $fd, $messageArr[$i], $connectionType);
            }
        }
    }

    private function tickerEvent($serv) {
        $startFd = $this->userbll->getLastCheckFd();
        while (true) {
            $conn_list = $serv->connection_list($startFd, 100);
            if ($conn_list === false or count($conn_list) === 0) {
                $this->userbll->writeLastCheckFd(0);
                echo "finish\n";
                break;
            }
            $startFd = end($conn_list);
            echo "\n";
            foreach ($conn_list as $fd) {
                if ($this->userbll->isConnectorTimeOut($fd)) {
                    $tem = "时间为" . date("Y-m-d H:i:s", time()) . "fd:" . $fd;
                    $this->writeLog($tem, "close/tickerEvent");
                    $serv->close($fd);
                }
            }
        }
    }

    /**
     * redis subscribe message send to user
     * @param $serv
     * @param $data
     * @return array
     */
    private function redisSend($serv, $data)
    {
        $msgArr = json_decode($data, true);
        $userId = intval($msgArr['dInfo']["userId"] ?? 0);
        $sn = $msgArr["data"]["sn"];

        $res = $this->userbll->getUserFdInfo($userId, $sn);

        $infoType = intval($msgArr["infoType"]);
        $sendDataArr = $msgArr["data"];
        $dInfoArr = isset($msgArr["dInfo"]) && $msgArr["dInfo"] ? $msgArr["dInfo"] : array();

        for ($i = 0; $i < count($res); $i++) {
            $this->selfSend($serv, $res[$i]["fd"], $this->getResultJson($infoType, "", $sendDataArr, $res[$i]["connectionType"], $dInfoArr), 0);
        }
    }

    /*
             * 组装完整的数据包并进行切割返回
    */
    private function getSplitDataList($serv, $fd, $data) {
        $dataCacheArr = $this->dataCacheTable->get("$fd");
        $content = "";
        if ($dataCacheArr) {
            $content = $dataCacheArr["content"];
        }
        $result = null;
        //如果原来还有数据则拼在一起
        if (!empty($content)) {
            $data = $content . $data;
        }
        //尚未接收到完整的包，先缓存起来
        if (!strstr($data, "#\t#")) {
            $this->dataCacheTable->set("$fd", array(
                "fd" => $fd,
                "content" => $data,
            ));
        } else {
            $result = explode("#\t#", $data);
            $count = count($result);
            if (!empty($result[$count - 1])) {
                //剔除完整包还有残余数据 继续留在缓存中
                $this->dataCacheTable->set("$fd", array(
                    "fd" => $fd,
                    "content" => $result[$count - 1],
                ));
                //返回结果中移除尚未完整的数据
                array_splice($result, $count - 1, 1);
            } else {
                //数据处理完，删除当前记录
                $this->dataCacheTable->del("$fd");
                //返回数组最后一个空数据
                array_splice($result, $count - 1, 1);
            }
        }
        return $result;
    }

    /**
     * 清除数据
     */
    private function clearCacheData($serv, $fd) {
        $this->dataCacheTable->del("$fd");
    }

    /*
             * 进行身份验证
             * 根据消息类型进行不同的处理
    */
    private function sendMessage($serv, $fd, $data, $connectionType) {
        $msgArr = json_decode($data, true);
        if (!is_array($msgArr)) {
            return;
        }
        if (array_key_exists("infoType", $msgArr) && array_key_exists("data", $msgArr)) {
            $infoType = intval($msgArr["infoType"]);
            $connectionType = isset($msgArr["connectionType"]) ? intval($msgArr["connectionType"]) : 1;
            $dataArr = $msgArr["data"];
            $sendDataArr = $msgArr["data"];
            $dInfoArr = isset($msgArr["dInfo"]) && $msgArr["dInfo"] ? $msgArr["dInfo"] : array();

            $message = isset($msgArr["message"]) ? $msgArr["message"] : "";

            if (!is_array($dataArr)) {
                $dataArr = $this->checkbll->decrypt($dataArr);
                $count = strrpos($dataArr, "}");
                $dataArr = substr($dataArr, 0, $count + 1);
                $dataArr = json_decode($dataArr, true);
                $sendDataArr = $dataArr;
            }

            $deviceType = isset($msgArr["deviceType"]) ? intval($msgArr["deviceType"]) : 0;
            $tem = $msgArr;
            $tem["data"] = $dataArr;
            if ($deviceType != CommonDefine::DEVICE_TYPE_ROBOT_HTTPS && $deviceType != CommonDefine::DEVICE_TYPE_SMART_HTTPS) {
                $tem = "接收 时间为：" . date("Y-m-d H:i:s", time()) . " 消息为：" . json_encode($tem, JSON_UNESCAPED_SLASHES) . "\r\n";
            }
            $this->writeLog($tem, $fd, $deviceType);

            if ($deviceType != CommonDefine::DEVICE_TYPE_ROBOT_HTTPS && $deviceType != CommonDefine::DEVICE_TYPE_SMART_HTTPS && $infoType != CommonDefine::INFO_TYPE_VALID && !$this->userbll->isValidConnector($fd)) {
                $tem = "时间为" . date("Y-m-d H:i:s", time()) . "fd:" . $fd;
                $this->writeLog($tem, "close/unValidConnector");
                $serv->close($fd);
            }

            switch ($infoType) {
                case CommonDefine::INFO_TYPE_VALID:
                    //$this->writeCheckLog($tem);
                    //身份验证
                    if (array_key_exists("token", $dataArr) && $this->userbll->validateUser($dataArr["token"])) {
                        $message = "ok";
                        if (array_key_exists("userId", $dataArr)) {
                            $userId = intval($dataArr["userId"]);
                            $res = $this->userbll->validateLoginUserNew($userId, $dataArr["token"]);
                            if ($res != 0) {
                                if ($res == 2) {
                                    //token过期
                                    $message = "E03000";
                                } else {
                                    $message = "E03001";
                                }
                            } else {
                                //添加到连接池中
                                $userFd = $this->userbll->saveConnector($fd, $connectionType, $userId, CommonDefine::DEVICE_TYPE_APP);
                                if ($userFd > 0) {
                                    $tem = "时间为" . date("Y-m-d H:i:s", time()) . "fd:" . $fd . "=userId=" . $userId;
                                    $this->writeLog($tem, "close/userFd");
                                    $serv->close($userFd);
                                }
                            }

                        } else {
                            if (!array_key_exists("sn", $dataArr)) {
                                return;
                            }
                            $sn = $dataArr["sn"];
                            //添加到连接池中
                            $this->userbll->saveConnector($fd, $connectionType, $sn, CommonDefine::DEVICE_TYPE_ROBOT);

                            //发送给订阅该机器的用户，告诉他在线
                            $toFdInfo = $this->userbll->getToFdInfo($fd, $dataArr);
                            $res = array("isExistConnect" => true, "type" => 1);
                            for ($i = 0; $i < count($toFdInfo); $i++) {
                                $this->selfSend($serv, $toFdInfo[$i]["fd"], $this->getResultJson("21006", "", $res, $toFdInfo[$i]["connectionType"]), 0);
                            }
                        }

                        $this->selfSend($serv, $fd, $this->getResultJson($infoType, $message, "", $connectionType), $deviceType);
                        return;
                    }
                    break;
                case CommonDefine::INFO_TYPE_USER_MATERIAL_SYNC:
                    //设备状态
                    $toFdInfo = $this->userbll->getDefaultToFdInfo($fd, $dataArr, $deviceType, $dInfoArr);
                    for ($i = 0; $i < count($toFdInfo); $i++) {
                        $this->selfSend($serv, $toFdInfo[$i]["fd"], $this->getResultJson($infoType, "", $sendDataArr, $toFdInfo[$i]["connectionType"], $dInfoArr), 0);
                    }
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "ok", "", $connectionType), $deviceType);
                    break;
                case CommonDefine::INFO_TYPE_MONSTER_FIGHTING_AWARD:
                    //查看是否在线
                    $res = $this->userbll->checkStatus($fd, $dataArr);
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "", $res, $connectionType), $deviceType);
                    break;
                case CommonDefine::INFO_TYPE_USER_UPDATE:
                    //更新用户状态（是否连上局域网）
                    $res = $this->userbll->updateUserInfo($fd, $dataArr);
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "", $res, $connectionType), $deviceType);
                case CommonDefine::MAP_DATA:
                    //地图
                    $toFdInfo = $this->userbll->getDefaultToFdInfo($fd, $dataArr, $deviceType, $dInfoArr);
                    for ($i = 0; $i < count($toFdInfo); $i++) {
                        $this->selfSend($serv, $toFdInfo[$i]["fd"], $this->getResultJson($infoType, "", $sendDataArr, $toFdInfo[$i]["connectionType"], $dInfoArr), 0);
                    }
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "ok", "", $connectionType), $deviceType);

                    break;
                case CommonDefine::INFO_TYPE_MESSAGE:
                    //消息通知
                    $toFdInfo = $this->userbll->getDefaultToFdInfo($fd, $dataArr, $deviceType, $dInfoArr);
                    for ($i = 0; $i < count($toFdInfo); $i++) {
                        $this->selfSend($serv, $toFdInfo[$i]["fd"], $this->getResultJson($infoType, "", $sendDataArr, $toFdInfo[$i]["connectionType"], $dInfoArr), 0);
                    }
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "ok", "", $connectionType), $deviceType);
                    break;
                case CommonDefine::INFO_TYPE_WARNING:
                    //绑定
                    $res = $this->userbll->bindMachine($fd, $dataArr);
                    $data = $this->checkbll->encrypt(json_encode($res["dataArr"]));
                    $this->selfSend($serv, $res["result"]["fd"], $this->getResultJson($infoType, "", $data, $res["result"]["connectionType"]), $deviceType);
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "ok", "", $connectionType), $deviceType);
                    break;
                case CommonDefine::INFO_TYPE_MACHINE_CLEAN_COUNTS:
                    //更新扫地机扫地拖地总数据
                    $res = $this->machinebll->updateMachineCleanData($fd, $dataArr);
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "", $res, $connectionType), $deviceType);
                    break;
                case CommonDefine::MAP_PATH:
                    //发送路径
                    if ($deviceType == CommonDefine::DEVICE_TYPE_ROBOT_HTTPS) {
                        $isHttp = isset($dInfoArr["isHttp"]) ? intval($dInfoArr["isHttp"]) : 0;
                        if ($isHttp) {
                            $ts = isset($dInfoArr["ts"]) ? $dInfoArr["ts"] : 0;
                            $sn = isset($dInfoArr["sn"]) ? $dInfoArr["sn"] : "";
                            $sendDataArr = $this->getMapRouteData($sn, $ts);
                            $dInfoArr = array("ts" => $dInfoArr["ts"], "userId" => $dInfoArr["userId"], "sn" => $sn);
                        }
                    }

                    $toFdInfo = $this->userbll->getDefaultToFdInfo($fd, $dataArr, $deviceType, $dInfoArr);

                    for ($i = 0; $i < count($toFdInfo); $i++) {
                        $this->selfSend($serv, $toFdInfo[$i]["fd"], $this->getResultJson($infoType, "", $sendDataArr, $toFdInfo[$i]["connectionType"], $dInfoArr), 0);
                    }
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "ok", "", $connectionType), $deviceType);
                    break;
                default:
                    $toFdInfo = $this->userbll->getDefaultToFdInfo($fd, $dataArr, $deviceType, $dInfoArr);
                    for ($i = 0; $i < count($toFdInfo); $i++) {
                        $this->selfSend($serv, $toFdInfo[$i]["fd"], $this->getResultJson($infoType, "", $sendDataArr, $toFdInfo[$i]["connectionType"], $dInfoArr), 0);
                    }
                    $this->selfSend($serv, $fd, $this->getResultJson($infoType, "ok", "", $connectionType), $deviceType);
                    break;

            }

        }
    }

    private function getMapRouteData($sn, $ts) {
        if (!$sn || !$ts) {
            return false;
        }

        $dir = CommonDefine::ROUTR_PATH;
        $filename = $dir . "/" . $sn . "/" . $ts;

        $data = file_get_contents($filename);
        $arr = json_decode($data, true);

        if ($arr) {
            $result = $arr["data"];
        } else {
            return false;
        }

        unlink($filename);
        return $result;
    }

    private function selfSend($serv, $fd, $message, $deviceType = 0) {
        if ($serv->exist($fd) && !empty($message)) {
            if ($deviceType == CommonDefine::DEVICE_TYPE_ROBOT_HTTPS || $deviceType == CommonDefine::DEVICE_TYPE_SMART_HTTPS) {
                // $message = json_encode($message) . "#\t#";
                // $serv->send($fd, $message);
                $serv->close($fd);
            } else {
                $tem = " 发送 时间为：" . date("Y-m-d H:i:s", time()) . " 消息为：" . json_encode($message, JSON_UNESCAPED_SLASHES) . "\r\n";
                $this->writeLog($tem, $fd);

                $fdInfo = $this->userbll->getConnector($fd);
                $deviceType = isset($fdInfo["deviceType"]) ? intval($fdInfo["deviceType"]) : 0;
                if ($deviceType == CommonDefine::DEVICE_TYPE_ROBOT) {
                    //如果 数据为 21006 或者 10001,直接返回不需要加密
                    if ($message["infoType"] == CommonDefine::INFO_TYPE_VALID || $message["infoType"] == CommonDefine::INFO_TYPE_MONSTER_FIGHTING_AWARD) {
                        $message = array("encrypt" => 0, "data" => $message);
                        $message = json_encode($message) . "#\t#";
                        //$this->writeCheckLog($tem);
                    } else {
                        $sn = $fdInfo["sn"];
                        $message = $this->getEnRobotMessage($message, $sn);
                    }

                    $serv->send($fd, $message);
                } else {
                    $message = json_encode($message) . "#\t#";
                    $serv->send($fd, $message);
                }
            }

        } else {
            echo "session $fd not exist\n";
        }
    }

    private function getEnRobotMessage($message, $sn) {
        $key = $this->getRobotKey($sn);
        $res = $this->checkbll->encrypt(json_encode($message), $key);
        $encdataArr = array("encrypt" => 1, "data" => $res);
        $res = json_encode($encdataArr);
        $result = $res . "#\t#";

        return $result;
    }

    public function writeLog($msg, $subDir = "", $deviceType = 0) {
        $logFilename = CommonDefine::LOG_DIR;
        //1.加上年月日
        $logFilename = $logFilename . "/" . date("Y", time()) . "/" . date("m", time()) . "/" . date("d", time());

        if (!empty($subDir)) {

            if ($deviceType == CommonDefine::DEVICE_TYPE_ROBOT_HTTPS) {

                var_dump($msg);
                $sn = $msg["data"]["sn"];
                if (!$sn) {
                    $sn = $msg["sn"];
                }
                $msg = "fd=-1   接收 时间为：" . date("Y-m-d H:i:s", time()) . " 消息为：" . json_encode($msg, JSON_UNESCAPED_SLASHES) . "\r\n";
                $logFilename = $logFilename . "/robot/" . $sn;
            } else {
                if (intval($subDir) > 0) {
                    $fd = intval($subDir);
                    //获取fd 的相关信息保存起来
                    $fdInfo = $this->userbll->getConnector($fd);

                    if ($fdInfo) {
                        $tem = '';
                        $msg = "fd=" . $fd . "   " . $msg;
                        if (array_key_exists("userId", $fdInfo) && intval($fdInfo["userId"]) > 0) {
                            $tem = $fdInfo["userId"];
                            $subTem = "user";
                            $logFilename = $logFilename . "/" . $subTem . "/" . $tem;
                        } else if (array_key_exists("sn", $fdInfo) && !empty($fdInfo["sn"])) {
                            $tem = $fdInfo["sn"];
                            $subTem = "robot";
                            $logFilename = $logFilename . "/" . $subTem . "/" . $tem;
                        }
                    } else {
                        //其他的消息
                        $logFilename = $logFilename . "/other";
                    }

                } else {
                    $logFilename = $logFilename . "/" . $subDir;
                }
            }
        }

        if (!file_exists($logFilename)) {
            mkdir($logFilename, 0755, true);
        }

        $fileName = $logFilename . "/" . date('Y-m-d H', time()) . ".txt";
        $fp = fopen($fileName, "a");
        fwrite($fp, $msg . "\n\n");
        fclose($fp);

    }

    public function writeCheckLog($msg) {
        $logFilename = CommonDefine::LOG_DIR;
        $filename = $logFilename . "/10001";

        if (!file_exists($filename)) {
            mkdir($filename, 0777, true);
        }

        $fileName = $filename . "/" . date('Y-m-d H', time()) . ".txt";
        $fp = fopen($fileName, "a");
        fwrite($fp, $msg . "\n\n");
        fclose($fp);

    }

    /*
             * websocket握手
    */
    private function wsHandShake($serv, $fd, $data) {
        //判断客户端类型 通过websocket握手时的关键词进行判断
        if (strpos($data, "Sec-WebSocket-Key") > 0) {
            $handShakeData = $this->getHandShakeData($data);
            $serv->send($fd, $handShakeData);
            return true;
        }
        return false;
    }

    /*
             * 获取返回结果
    */
    private function getResultJson($infoType, $message, $data, $connectionType = CONNECTION_TYPE_SOCKET, $dInfoArr = array()) {
        $apiResult = array(
            "infoType" => $infoType,
            "data" => $data,
        );

        if ($dInfoArr) {
            $apiResult["dInfo"] = $dInfoArr;
        }

        if (!empty($message)) {
            $apiResult["message"] = $message;
        }

        return $apiResult;
    }

    private function getRobotKey($sn) {
        if (!$sn) {
            return false;
        }
        $filename = CommonDefine::CONNECTOR_KEY . "/" . $sn;
        if (!file_exists($filename)) {
            return false;
        }
        $key = file_get_contents($filename);
        return $key;
    }
}

$server = new SweepingSwooleWebSocketServer();
$server->run();