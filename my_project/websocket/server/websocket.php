<?php

	/*
	 * websocket服务端类 基于扩展
	 * james add 2015-0813
	 * swoole 1.7.18
	 */
	class Websocket
	{
		public $host; // 监听地址
		public $port; // 监听端口
		public $config; // swoole配置
		public $server; // swoole server
		public $public_error = array("status" => false, "code" => "", "data" => "", "msg" => "");

		/**
		 * 初始化swoole参数
		 * @param unknown $host
		 * @param unknown $port
		 */
		function __construct()
		{
			$this->config = require_once 'config.php';
			$this->host = $this->config['swoole_server_host'];
			$this->port = $this->config['swoole_server_port'];
		}

		/**
		 * 启动服务
		 */
		function run()
		{
			$this->server = new swoole_websocket_server($this->host, $this->port);
			$this->server->set($this->config['swoole_setting']);
			$this->server->addlistener($this->host, 9002, SWOOLE_SOCK_TCP);
			$this->server->on('Message', array($this, 'onReceive'));
			$this->server->on('Request', array($this, 'onRequest'));
			$this->server->on('Close', array($this, 'onClose'));
			$this->server->on('Start', array($this, 'onStart'));
			//$this->server->on('Connect', array($this, 'onConnect'));
			$this->server->on('Open', array($this, 'onOpen'));
			$this->server->on('WorkerStart', array($this, 'onWorkerStart'));
			$this->server->on('WorkerStop', array($this, 'onWorkerStop'));
			$this->server->on('WorkerError', array($this, 'onWorkerError'));
			$this->server->on('Shutdown', array($this, 'onShutdown'));
			$this->server->on('Task', array($this, 'onTask'));
			$this->server->on('Timer', array($this, 'onTimer'));
			$this->server->on('Finish', array($this, 'onFinish'));
			$this->server->on('ManagerStart', array($this, 'onManagerStart'));
			$this->server->start();
		}

		/**
		 * @param $serv
		 * @param $frame
		 * @return bool
		 * echo "message: ".$frame->data;
		 * $server->push($frame->fd, json_encode(["hello", "world"]));
		 */
		function onRequest($request, $response)
		{
			$post = isset($request->post) ? $request->post : array();
			if (!$post || !isset($post['web_key']) || $post['web_key'] != "sdsdsdsdsdsdwewewewesd232323232323") {
				return $this->request_end($request, $response);
			}
			$data = json_decode($post['data'], true);
			if (!$data) {
				return $this->request_end($request, $response);
			}
			$this->onAdmin($data, $request->fd);
			;
			return $this->request_end($request, $response);
		}

		function request_end($request, $response)
		{
			$response->write("1");
			$response->end();
			return false;
		}

		/**
		 * @param $serv
		 * @param $frame
		 * @return bool
		 * echo "message: ".$frame->data;
		 * $server->push($frame->fd, json_encode(["hello", "world"]));
		 */
		function onReceive($serv, $frame)
		{
			$data = json_decode($frame->data, true);
			if (!$data) {
				return false;
			}
			$this->onMessage($serv, $frame->fd, $data);
		}

		function onOpen($svr, $req)
        {
        }

		/**
		 * 接收到websocket数据时触发
		 * @param unknown $fd
		 * @param unknown $data
		 */
		function onMessage($serv, $fd, $data)
		{
		}

		function onTimer($serv, $interval)
		{
		}

		/**
		 * 关闭一个连接时触发
		 * @param unknown $serv
		 * @param unknown $fd
		 * @param unknown $from_id
		 */
		function onClose($serv, $fd, $from_id)
		{
		}

		/**
		 * 管理者专消息
		 * @param $fd
		 * @param $data
		 * @return mixed
		 */
		function onAdmin($data, $fd = 0)
		{
		}

		/**
		 * 主进程开始
		 * @param unknown $serv
		 * @param unknown $worker_id
		 */
		function onStart($serv)
		{
			global $argv;
			swoole_set_process_name("{$argv[0]} {$this->host}:{$this->port} master");
		}

		/**
		 * 工作组开始
		 * @param unknown $serv
		 * @param unknown $worker_id
		 */
		function onWorkerStart($serv, $worker_id)
		{
			$this->processRename($serv, $worker_id);
		}

		/**
		 * @param $serv
		 * @param $worker_id
		 * 工作组进程停止
		 */
		function onWorkerStop($serv, $worker_id)
		{
		}

		/**
		 * @param $serv
		 * @param $worker_id
		 * @param $worker_pid
		 * @param $exit_code
		 * 工作组进程出错
		 */
		function onWorkerError($serv, $worker_id, $worker_pid, $exit_code)
		{
			echo "worker abnormal exit. WorkerId=$worker_id|Pid=$worker_pid|ExitCode=$exit_code\n";
		}

		/**
		 * @param $serv
		 * @param $task_id
		 * @param $from_id
		 * @param $data
		 * 任务开始
		 */
		function onTask($serv, $task_id, $from_id, $data)
		{
		}

		/**
		 * @param $serv
		 * @param $task_id
		 * @param $data
		 * 任务完成
		 */
		function onFinish($serv, $task_id, $data)
		{
		}

		/**
		 * @param $serv
		 * 关闭
		 */
		function onShutdown($serv)
		{
			echo PHP_EOL . date("Y-m-d H:i:s") . " server shutdown!" . PHP_EOL;
		}

		/**
		 * @param $serv
		 * 主进程
		 */
		function onManagerStart($serv)
		{
			global $argv;
			swoole_set_process_name("{$argv[0]} {$this->host}:{$this->port} manager");
		}

		function processRename($serv, $worker_id)
		{
			global $argv;
			if ($worker_id >= $serv->setting['worker_num']) {
				swoole_set_process_name("{$argv[0]} {$this->host}:{$this->port} task");
			} else {
				swoole_set_process_name("{$argv[0]} {$this->host}:{$this->port} worker");
			}
		}

        /**
        * 根据客户端连接时发过来的消息提取Sec-WebSocket-Key
        */
        private function getHeaders($data)
        {
            $r = $h = $o = null;
            if(preg_match("/GET (.*) HTTP/"   , $data, $match))
            {
                $r = $match[1];
            }
            if(preg_match("/Host: (.*)\r\n/"  , $data, $match))
            {
                $h = $match[1];
            }
            if(preg_match("/Origin: (.*)\r\n/", $data, $match))
            {
                $o = $match[1];
            }
            if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match))
            {
                $key = $match[1];
            }

            return array($r, $h, $o, $key);
        }

        /**
        * 服务端根据Sec-WebSocket-Key生成握手数据
        */
        public function getHandShakeData($buffer)
        {
            list($resource, $host, $origin, $key) = $this->getHeaders($buffer);

            //websocket version 13
            $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

            $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
                "Upgrade: websocket\r\n" .
                "Sec-WebSocket-Version: 13\r\n".
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: " . $acceptKey . "\r\n\r\n";  //必须以两个回车结尾

            return $upgrade;
        }


        public function wrap($msg="", $opcode = 0x1)
        {
            //默认控制帧为0x1（文本数据）
            $firstByte = 0x80 | $opcode;
            $encodedata = null;
            $len = strlen($msg);

            if (0 <= $len && $len <= 125)
            {
                $encodedata = chr(0x81) . chr($len) . $msg;
            }
            else if (126 <= $len && $len <= 0xFFFF)
            {
                $low = $len & 0x00FF;
                $high = ($len & 0xFF00) >> 8;
                $encodedata = chr($firstByte) . chr(0x7E) . chr($high) . chr($low) . $msg;
            }
            else
            {
                return chr(0x81).chr(127).pack("xxxxN", $len).$msg;
            }
            return $encodedata;

        }

        public function unwrap($msg="")
        {
            $opcode = ord(substr($msg, 0, 1)) & 0x0F;
            $payloadlen = ord(substr($msg, 1, 1)) & 0x7F;
            $ismask = (ord(substr($msg, 1, 1)) & 0x80) >> 7;
            $maskkey = null;
            $oridata = null;
            $decodedata = null;

            //数据不合法
            if ($ismask != 1 || $opcode == 0x8)
            {
                return false;
            }

            //获取掩码密钥和原始数据
            if ($payloadlen <= 125 && $payloadlen >= 0)
            {
                $maskkey = substr($msg, 2, 4);
                $oridata = substr($msg, 6);
            }
            else if ($payloadlen == 126)
            {
                $maskkey = substr($msg, 4, 4);
                $oridata = substr($msg, 8);
            }
            else if ($payloadlen == 127)
            {
                $maskkey = substr($msg, 10, 4);
                $oridata = substr($msg, 14);
            }
            $len = strlen($oridata);
            for($i = 0; $i < $len; $i++)
            {
                $decodedata .= $oridata[$i] ^ $maskkey[$i % 4];
            }
            return $decodedata;
        }
	}
