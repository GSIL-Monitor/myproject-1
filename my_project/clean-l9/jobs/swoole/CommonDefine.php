<?php
class CommonDefine {

	const CONNECTOR_DIR = "/mnt/www/clean/jobs/swoole/connections/";
	const CONNECTOR_KEY = "/mnt/www/clean/jobs/swoole/key";
	const AVATAR_URL = "http://s.imscv.com:8001/avatar";
	const LOG_DIR = "/mnt/www/clean/jobs/swoole/var";
	const ROUTR_PATH = "/mnt/www/upload/clean-map-path";

	const INFO_TYPE_VALID = 10001;
	const INFO_TYPE_WARNING = 10002;
	const INFO_TYPE_USER_UPDATE = 10006;

	const INFO_TYPE_USER_MATERIAL_SYNC = 20001;
	const MAP_DATA = 20002;
	const INFO_TYPE_MESSAGE = 20003;
	const INFO_TYPE_MONSTER_FIGHTING_JOIN = 21001;
	const INFO_TYPE_MONSTER_FIGHTING_QUIT = 21002;
	const INFO_TYPE_MONSTER_FIGHTING_ATTACK = 21003;
	const INFO_TYPE_MONSTER_FIGHTING_USER = 21004;
	const INFO_TYPE_MONSTER_FIGHTING_BLOOD = 21005;
	const INFO_TYPE_MONSTER_FIGHTING_AWARD = 21006;
	const INFO_TYPE_MONSTER_INFO = 21007;
	const INFO_TYPE_MACHINE_CLEAN_COUNTS = 21009;
	const SPEED_CONTROL = 22001;
	const INFO_TYPE_LAND_DETAIL = 22002;
	const INFO_TYPE_LAND_NEARBY = 22003;
	const MAP_PATH = 21011;

	const CONNECTION_TYPE_SOCKET = 1;
	const CONNECTION_TYPE_WEBSOCKET = 2;

	const File_TYPE_FD = 1;
	const File_TYPE_USER = 2;
	const File_TYPE_ROBOT = 2;

	const DEVICE_TYPE_APP = 1;
	const DEVICE_TYPE_ROBOT = 2;
	const DEVICE_TYPE_ROBOT_HTTPS = 3;
	const DEVICE_TYPE_SMART_HTTPS = 4; //智能设备

	// 字符串拼接字符
	const SPLIT_WORD = "@";

}