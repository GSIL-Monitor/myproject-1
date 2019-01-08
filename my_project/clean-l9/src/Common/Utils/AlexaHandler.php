<?php

namespace Common\Utils;

class AlexaHandler {

	static public function swooleClient($data, $port = 9501) {

		set_time_limit(60);
		$ip = "39.107.127.173";

		/*
			        +-------------------------------
			         *    @socket连接整个过程
			        +-------------------------------
			         *    @socket_create
			         *    @socket_connect
			         *    @socket_write
			         *    @socket_read
			         *    @socket_close
			        +--------------------------------
		*/

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		$result = array();
		if ($socket < 0) {
			$result["message"] = socket_strerror($socket);
			$result["code"] = "E00";
			return $result;
		}

		$res = socket_connect($socket, $ip, $port);

		if ($res < 0) {
			$result["message"] = socket_strerror($res);
			$result["code"] = "E01";
			return $result;
		}

		$out = '';

		if (!socket_write($socket, $data, strlen($data))) {
			$result["message"] = socket_strerror($socket);
			$result["code"] = "E02";
			return $result;
		}

		while ($out = socket_read($socket, 8192)) {
			$result["data"] = $out;
			$result["code"] = "N00";
			break;
		}

		//$close = socket_close($socket);
		usleep(50000);

		return $result;
	}
}

?>