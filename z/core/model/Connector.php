<?php

namespace z\core\model;

use Redis;
use mysqli;

use z\core\{
	Config,
	Log
};

/**
 * 连接类(基于集群)
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Connector
{
	private static $_instance;
	private function __construct(){}
	//禁止用户复制对象实例
	public function __clone(){
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	/**
	 * 单例构造方法
	 * 
	 * @access public
	 * @return class
	 */
	public static function init(){
		if(!self::$_instance){
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	/**
	 * 设置指定集群配置项
	 * 
	 * @access public
	 * @param  string  $strTag     集群标记(如redis、mysql等)
	 * @return class
	 */
	public function setConfig($strTag){
		$clusterCfgName = $strTag . '_clusterCfg';
		if(isset($this->$clusterCfgName)){
			return $this;
		}
		$clusterObjName = $strTag . '_clusterObj';
		$clusterMapName = $strTag . '_clusterMap';
		$clusterErrName = $strTag . '_clusterErr';
		$this->$clusterCfgName = Config::loadConfig($strTag . 'Config');
		$this->$clusterObjName = [];
		$this->$clusterMapName = [];
		$this->$clusterErrName = [];
		return $this;
	}
	
	/**
	 * 获得一个可用配置集
	 * 
	 * @access public
	 * @param  string  $strTag        集群标记(如redis、mysql等)
	 * @param  string  $strStoreType  存储类型
	 * @param  bool    $boolMaster    是否主机（写操作）
	 * @return array
	 */
	public function connect($strTag, $strStoreType, $boolMaster = true){
		$clusterCfgName = $strTag . '_clusterCfg';
		if(!isset($this->$clusterCfgName)){
			trigger_error('该集群配置尚未被设置', E_USER_ERROR);
		}
		$clusterObjName = $strTag . '_clusterObj';
		$clusterMapName = $strTag . '_clusterMap';
		$clusterErrName = $strTag . '_clusterErr';
		//如果标记了错误，那么本次请求的所有该存储类型的配置都是不可用的
		if(isset($this->$clusterErrName[$strStoreType])){
			return false;
		}
		//构造映射的键名
		$mapKey = ($boolMaster ? 'master' : 'salve') . '_' . $strStoreType;
		//如果存在映射，立即返回对应的redis对象
		if(isset($this->$clusterMapName[$mapKey])){
			return $this->$clusterObjName[$this->$clusterMapName[$mapKey]['objIdx']];
		}
		//取得主机配置
		if($boolMaster){
			$serverType = 'master';
			$strStoreType = empty($this->$clusterCfgName['master'][$strStoreType]) ? 'default' : $strStoreType;
		}
		//取得从机配置
		else{
			$serverType = 'salve';
			//尝试匹配指定类型的从机
			if(empty($this->$clusterCfgName['salve'][$strStoreType])){
				$serverType = 'master';
				//匹配不到指定类型的从机，尝试匹配该类型的主机
				if(empty($this->$clusterCfgName['master'][$strStoreType])){
					$serverType = 'salve';
					$strStoreType = 'default';
					//匹配不到指定类型的主机时，尝试匹配默认的从机
					if(empty($this->$clusterCfgName['salve']['default'])){
						$serverType = 'master';
					}
				}
			}
		}
		//检查在映射中是否有不同键名，但使用相同配置的情况，有则增加对应映射，并返回redis对象
		foreach($this->$clusterMapName as $k => $v){
			if($v['serverType'] == $serverType && $v['storeType'] == $strStoreType){
				$this->$clusterMapName[$mapKey] = $v;
				return $this->$clusterObjName[$v['objIdx']];
			}
		}
		//取得对应的连接配置
		$targetCfg = $this->$clusterCfgName[$serverType][$strStoreType];
		//主服务器仅一个配置，转为数组形式
		if($boolMaster){
			$targetCfg = [$targetCfg];
		}
		//初始化连接对象
		$connectObj = [];
		switch($strTag){
			case CLUSTER_CONNECT_TYPE_MYSQL:
				$connectObj = mysqli_init();
				break;
			case CLUSTER_CONNECT_TYPE_REDIS:
				$connectObj = new Redis();
				break;
		}
		//屏蔽掉尝试连接时可能产生的报错
		$oldReportCode = error_reporting(0);
		//尝试连接
		$state = false;
		do{
			//随机取一个配置项进行连接，直到成功或尝试完所有配置项(简陋且不可靠的负载策略-.-)
			$subKey = array_rand($targetCfg);
			$tmpCfg = $targetCfg[$subKey];
			switch($strTag){
				case CLUSTER_CONNECT_TYPE_MYSQL:
					//一台服务器可能有多个库，所以这里只负责连服务器，然后外部使用USE语句切换指定库
					$state = $connectObj->real_connect($tmpCfg['dbhost'], $tmpCfg['dbuser'], $tmpCfg['dbpwd'], '', $tmpCfg['dbport']);
					break;
				case CLUSTER_CONNECT_TYPE_REDIS:
					$state = $connectObj->connect($tmpCfg[0], $tmpCfg[1]);
					if(isset($tmpCfg[2])){
						$connectObj->auth($tmpCfg[2]);
					}
					break;
			}
			unset($targetCfg[$subKey]);
		}
		while(!$state && !empty($targetCfg));
		//还原错误报告的级别设置
		error_reporting($oldReportCode);
		//成功连接
		if($state){
			$this->$clusterMapName[$mapKey] = [
				'serverType'	=> $serverType,
				'storeType'		=> $strStoreType,
				'objIdx'		=> count($this->$clusterObjName)
			];
			$this->$clusterObjName[] = $connectObj;
			return $connectObj;
		}
		//失败
		else{
			$this->$clusterErrName[$strStoreType] = '';
			$content = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $strStoreType;
			Log::save($strTag . 'FailureLog', $content);
			return false;
		}
	}
}
