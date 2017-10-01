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
	private static $_instance;//类实例
	//禁止直接创建对象
	private function __construct($boolCurrent, $boolUseHttps)
	{
		self::$domain = Request::getDomain($boolCurrent);
		self::$isHttps = $boolUseHttps;
	}
	//禁止用户复制对象实例
	public function __clone()
	{
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	/**
	 * 单例构造方法
	 * 
	 * @access public
	 * @return this
	 */
	public static function init($boolCurrent = false, $boolUseHttps = false)
	{
		if(!self::$_instance)
		{
			$c = __CLASS__;
			self::$_instance = new $c($boolCurrent, $boolUseHttps);
		}
		return self::$_instance;
	}
	
	/**
	 * 设置cookie
	 * 
	 * @access public
	 * @param  string  $key    键名
	 * @param  string  $value  键值
	 * @return boolean
	 */
	public static function set($key, $value)
	{
		return setcookie($key, $value, z::$configure['cookie_expire'], '/', self::$domain, self::$isHttps);
	}
	
	/**
	 * 获取cookie值
	 * 
	 * @access public
	 * @param  string  $key    键名
	 * @return value/boolean
	 */
	public static function get($key)
	{
		if(empty($_COOKIE[$key]))
		{
			return false;
		}
		return $_COOKIE[$key];
	}
	
	/**
	 * 删除cookie值
	 * 
	 * @access public
	 * @param  string  $key    键名
	 * @return boolean
	 */
	public static function delete($key)
	{
		return setcookie($key, NULL, -1);
	}
}
