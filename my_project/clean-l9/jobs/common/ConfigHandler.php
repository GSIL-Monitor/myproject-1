<?php
require_once dirname(dirname(__FILE__)).'/config/Config.php';
require_once dirname(dirname(__FILE__)).'/common/FileHandler.php';

class ConfigHandler
{
	static public function getDatabaseConfig()
	{
		$filename=dirname(dirname(__FILE__)).'/config/'.Config::ENVIRONMENT.'/DatabaseConfig.json';
		$result=FileHandler::getArrayFromJsonFile($filename);
		return $result;
	}

	static public function getCommonConfigs($key = '')
	{
		$filename=dirname(dirname(__FILE__)).'/config/'.Config::ENVIRONMENT.'/CommonConfig.json';
		$result=FileHandler::getArrayFromJsonFile($filename);
		if($key){
			return $result[$key];
		}else{
			return $result;
		}
		
	}
}