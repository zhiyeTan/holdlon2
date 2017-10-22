<?php
namespace z\core;

use Redis;
use z\lib\Basic;

/**
 * 远程字典服务器集群
 * Remote dictionary server cluster
 * 
 * 一主多从：一个写服务器，一个或多个读服务器。配置主从复制（全量同步和增量同步）
 *          使用同一核心配置时，相当于一主多从。当子应用出现不同配置时，相当于多主多从。
 *          本质上，一个应用只有一个主服务器！
 * 
 * 多主多从：一个应用允许有多个主从服务器，向下兼容了一主多从。
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Redisc{
	private static $clusterCfg = [];//集群配置
	private static $clusterObj = [];//集群对象[redis1, redis2, redis3...]
	private static $clusterMap = [];//集群配置与对象的映射[$serverType.'_'.$storeType=>[serverType=>string, storeType=>string, objIdx=>int]]
	private static $_instance;//类实例
	//私有的构造函数
	private function __construct(){
		self::$clusterCfg = Config::$redisConfig;
	}
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
		if (!self::$_instance) {
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	/**
	 * 连接远程服务器
	 * @access private
	 * @param  string  $strType     类型
	 * @param  bool    $boolMaster  是否主机（写操作）
	 * @return class
	 */
	private static function connect($strType, $boolMaster = true){
		$storeType = $strType;
		//构造映射的键名
		$mapKey = ($boolMaster ? 'master' : 'salve') . '_' . $storeType;
		//如果存在映射，立即返回对应的redis对象
		if(isset(self::$clusterMap[$mapKey])){
			return self::$clusterObj[self::$clusterMap[$mapKey]['objIdx']];
		}
		//取得从机配置
		if(!$boolMaster){
			$serverType = 'salve';
			//尝试匹配指定类型的从机
			if(empty(self::$clusterCfg['salve'][$storeType])){
				$serverType = 'master';
				//匹配不到指定类型的从机，尝试匹配该类型的主机
				if(empty(self::$clusterCfg['master'][$storeType])){
					$serverType = 'salve';
					$storeType = 'default';
					//匹配不到指定类型的主机时，尝试匹配默认的从机
					if(empty(self::$clusterCfg['salve']['default'])){
						$serverType = 'master';
					}
				}
			}
		}
		//取得主机配置
		if($boolMaster){
			$serverType = 'master';
			$storeType = empty(self::$clusterCfg['master'][$strType]) ? 'default' : $strType;
		}
		//构建新的映射键名
		$newMapKey = $serverType . '_' . $storeType;
		//如果存在映射键名，立即返回对应的redis对象
		if(isset(self::$clusterMap[$newMapKey])){
			return self::$clusterObj[self::$clusterMap[$newMapKey]['objIdx']];
		}
		//检查在映射中是否有不同键名，但使用相同配置的情况，有则增加对应映射，并返回redis对象
		foreach(self::$clusterMap as $k => $v){
			if($v['serverType'] == $serverType && $v['storeType'] == $storeType){
				self::$clusterMap[$newMapKey] = $v;
				return self::$clusterObj[$v['objIdx']];
			}
		}
		
		$newObjIdx = count(self::$clusterObj);
		//主服务器仅一个配置
		$targetCfg = self::$clusterCfg[$serverType][$storeType];
		//从服务器可能存在多个配置，取得其中一个
		//TODO 如果是从属服务器，应该随机从服务器列表中抽取一个配置进行连接，直到成功或遍历结束
		if($serverType == 'salve'){
			$max = count($targetCfg);
			$currIdx = $max > self::$randIdx ? $max % self::$randIdx : self::$randIdx % $max;
			$targetCfg = $targetCfg[$currIdx];
		}
		$redis = new Redis();
		$redis->connect($targetCfg[0], $targetCfg[1]);
		self::$clusterObj[] = $redis;
		self::$clusterMap[$newMapKey] = array(
			'serverType'	=> $serverType,
			'storeType'		=> $storeType,
			'objIdx'		=> $newObjIdx
		);
		return $redis;
	}
	
	/**
	 * 获取键值
	 * 
	 * @access public
	 * @param  string  $strKey     键名
	 * @param  string  $strType    类型
	 * @param  bool    $boolAssoc  是否取得数组
	 * @return string/array
	 */
	public static function get($strKey, $strType = 'default', $boolAssoc = false){
		$redis = self::connect($strType, false);
		$value = $redis->get($strKey);
		return $boolAssoc ? json_decode($value, true) : $value;
	}
	
	/**
	 * 设置键值
	 * 
	 * @access public
	 * @param  string  $strKey   键名
	 * @param  string  $nValue   键值
	 * @param  string  $strType  类型
	 */
	public static function set($strKey, $nValue, $strType = 'default', $expire = null){
		$redis = self::connect($strType);
		$value = is_array($nValue) ? json_encode($nValue) : $nValue;
		if($expire && is_int($expire)){
			$redis->setex($strKey, $expire, $value);
		}
		else{
			$redis->set($strKey, $value);
		}
	}
	
	/**
	 * 通过事务来设置键值
	 * 
	 * @access public
	 * @param  string  $strKey   键名
	 * @param  string  $nValue   键值
	 * @param  string  $strType  类型
	 */
	public static function setByTransaction($strKey, $nValue, $strType = 'default', $expire = null){
		$redis = self::connect($strType);
		$value = is_array($nValue) ? json_encode($nValue) : $nValue;
		//监听键名
		$redis->watch($strKey);
		//开启事务
		$redis->multi();
		if($expire && is_int($expire)){
			$redis->setex($strKey, $expire, $value);
		}
		else{
			$redis->set($strKey, $value);
		}
		$redis->incr($strKey);
		if(!$redis->exec()){
			//取消事务
			$redis->discard();
		}
		//停止监听
		$redis->unwatch($strKey);
	}
	
	/**
	 * 设置键值
	 * 
	 * @access public
	 * @param  string  $strKey   键名
	 * @param  string  $strType  类型
	 */
	public static function del($strKey, $strType = 'default'){
		$redis = self::connect($strType);
		$redis->delete($strKey);
	}
}
