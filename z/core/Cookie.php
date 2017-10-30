<?php

namespace z\core;

use z;

/**
 * cookie管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Cookie
{
	private static $domain;//作用域
	private static $isHttps;//是否通过HTTPS传输
	private static $expire;//有效期
	private static $_instance;//类实例
	//禁止直接创建对象
	private function __construct($boolCurrent, $boolUseHttps){
		self::$domain = Request::getDomain($boolCurrent);
		self::$isHttps = $boolUseHttps;
		self::$expire = Config::$options['cookie_expire'];
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
	public static function init($boolCurrent = false, $boolUseHttps = false){
		if(!self::$_instance){
			$c = __CLASS__;
			self::$_instance = new $c($boolCurrent, $boolUseHttps);
		}
		return self::$_instance;
	}
	
	/**
	 * 设置cookie
	 * 
	 * @access public
	 * @param  string  $strKey  键名
	 * @param  mixed   $nValue  键值
	 * @return boolean
	 */
	public static function set($strKey, $nValue){
		$value = is_array($nValue) ? json_encode($nValue) : $nValue;
		return setcookie($strKey, $value, self::$expire, '/', self::$domain, self::$isHttps);
	}
	
	/**
	 * 获取cookie值
	 * 
	 * @access public
	 * @param  string  $strKey     键名
	 * @param  bool    $boolAssoc  是否取得数组
	 * @return value/boolean
	 */
	public static function get($strKey, $boolAssoc = false){
		if(empty($_COOKIE[$strKey])){
			return false;
		}
		return $boolAssoc ? json_decode($_COOKIE[$strKey], true) : $_COOKIE[$strKey];
	}
	
	/**
	 * 删除cookie值
	 * 
	 * @access public
	 * @param  string  $strKey  键名
	 * @return boolean
	 */
	public static function del($strKey){
		return setcookie($strKey, NULL, -1);
	}
}
