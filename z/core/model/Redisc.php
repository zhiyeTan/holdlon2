<?php
namespace z\core\model;

use z\lib\Basic;

/**
 * 远程字典服务器集群
 * Remote dictionary server cluster
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Redisc
{
	private static $tagName = 'redis';
	private static $_instance;//类实例
	//私有的构造函数
	private function __construct(){
		Connector::init()->setConfig(self::$tagName);
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
	 * 获取键值
	 * 
	 * @access public
	 * @param  string  $strKey   键名
	 * @param  string  $strType  类型
	 * @return string/array
	 */
	public static function get($strKey, $strType = 'default'){
		$redis = Connector::init()->connect(self::$tagName, $strType, false);
		if(!$redis){
			return false;
		}
		$value = $redis->get($strKey);
		$jsonData = $value ? json_decode($value, true) : null;
		return $jsonData === null ? $value : $jsonData;
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
		$redis = Connector::init()->connect(self::$tagName, $strType);
		if($redis){
			$value = is_array($nValue) ? json_encode($nValue) : $nValue;
			if($expire && is_int($expire)){
				$redis->setex($strKey, $expire, $value);
			}
			else{
				$redis->set($strKey, $value);
			}
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
		$redis = Connector::init()->connect(self::$tagName, $strType);
		if($redis){
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
	}
	
	/**
	 * 设置键值
	 * 
	 * @access public
	 * @param  string  $strKey   键名
	 * @param  string  $strType  类型
	 */
	public static function del($strKey, $strType = 'default'){
		$redis = Connector::init()->connect(self::$tagName, $strType);
		if($redis){
			$redis->delete($strKey);
		}
	}
}
