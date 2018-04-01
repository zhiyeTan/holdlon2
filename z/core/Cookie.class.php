<?php
/**
 * cookie管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreCookieCls
{
	private static $domain;//作用域
	private static $isHttps;//是否通过HTTPS传输
	private static $expire;//有效期
	private static $_instance;//类实例
	//禁止直接创建对象
	private function __construct($withSuffix, $useHttps){
		self::$isHttps = $useHttps;
		self::$domain = zCoreRequestCls::getDomain($withSuffix);
		self::$expire = zCoreConfigCls::$options['cookie_expire'];
	}
	//禁止用户复制对象实例
	public function __clone(){
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	/**
	 * 单例构造方法
	 * @access public
	 * @param  bool  $withSuffix  是否包含域名前缀
	 * @param  bool  $useHttps    是否使用HTTPS协议
	 * @return class
	 */
	public static function init($withSuffix = false, $useHttps = false){
		if(!self::$_instance){
			$c = __CLASS__;
			self::$_instance = new $c($withSuffix, $useHttps);
		}
		return self::$_instance;
	}
	
	/**
	 * 设置cookie
	 * @access public
	 * @param  string  $key    键名
	 * @param  mixed   $value  键值
	 * @return boolean
	 */
	public static function set($key, $value){
		$value = is_array($value) ? json_encode($value) : $value;
		return setcookie($key, $value, self::$expire, '/', self::$domain, self::$isHttps);
	}
	
	/**
	 * 获取cookie值
	 * @access public
	 * @param  string  $key  键名
	 * @return value/boolean
	 */
	public static function get($key){
		if(empty($_COOKIE[$key])){
			return false;
		}
		$data = $_COOKIE[$key] ? json_decode($_COOKIE[$key], true) ? null;
		return $data === null ? $_COOKIE[$key] : $data;
	}
	
	/**
	 * 删除cookie值
	 * @access public
	 * @param  string  $key  键名
	 * @return boolean
	 */
	public static function del($key){
		return setcookie($key, NULL, -1);
	}
}
