<?php
/**
 * redis
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zModRedisCls
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
	 * @access public
	 * @param  string  $key   键名
	 * @param  string  $type  类型
	 * @return string/array
	 */
	public static function get($key, $type = 'default'){
		$redis = Connector::init()->connect(self::$tagName, $type, false);
		if(!$redis){
			return false;
		}
		$value = $redis->get($key);
		$jsonData = $value ? json_decode($value, true) : null;
		return $jsonData === null ? $value : $jsonData;
	}
	
	/**
	 * 设置键值
	 * @access public
	 * @param  string  $key    键名
	 * @param  string  $value  键值
	 * @param  string  $type   类型
	 */
	public static function set($key, $value, $type = 'default', $expire = null){
		$redis = Connector::init()->connect(self::$tagName, $type);
		if($redis){
			$value = is_array($value) ? json_encode($value) : $value;
			if($expire && is_int($expire) && $expire > 0){
				$redis->setex($key, $expire, $value);
			}
			else{
				$redis->set($key, $value);
			}
		}
	}
	
	/**
	 * 通过事务来设置键值
	 * @access public
	 * @param  string  $key    键名
	 * @param  string  $value  键值
	 * @param  string  $type   类型
	 */
	public static function setByTransaction($key, $value, $type = 'default', $expire = null){
		$redis = Connector::init()->connect(self::$tagName, $type);
		if($redis){
			$value = is_array($value) ? json_encode($value) : $value;
			//监听键名
			$redis->watch($key);
			//开启事务
			$redis->multi();
			if($expire && is_int($expire)){
				$redis->setex($key, $expire, $value);
			}
			else{
				$redis->set($key, $value);
			}
			$redis->incr($key);
			if(!$redis->exec()){
				//取消事务
				$redis->discard();
			}
			//停止监听
			$redis->unwatch($key);
		}
	}
	
	/**
	 * 设置键值
	 * @access public
	 * @param  string  $key   键名
	 * @param  string  $type  类型
	 */
	public static function del($key, $type = 'default'){
		$redis = Connector::init()->connect(self::$tagName, $type);
		if($redis){
			$redis->delete($key);
		}
	}
}
