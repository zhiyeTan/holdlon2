<?php
namespace z\core;

/**
 * 配置管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Config{
	public static $options = []; //配置项
	private static $_instance; //实例
	//私有构造方法
	private function __construct(){
		self::$options = require UNIFIED_PATH . 'z' . Z_DS . 'config' . Z_DS . 'config.php';
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
	 * 设置调试模式
	 * 
	 * @access public
	 * @param  bool    $bool   默认true
	 */
	public static function setDebugModel($bool = true){
		error_reporting($bool ? E_ALL : 0);
	}
	/**
	 * 加载预定义常量
	 * 
	 * @access public
	 */
	public static function loadConstant(){
		require UNIFIED_PATH . 'z' . Z_DS . 'define' . Z_DS . 'define.php';
	}
	/**
	 * 加载应用重定义的配置
	 * 
	 * @access public
	 */
	public static function loadAppConfig(){
		$appConfigFile = APP_PATH . 'config' . Z_DS . 'config.php';
		if(is_file($appConfigFile)){
			self::$options = array_merge(self::$options, include $appConfigFile);
		}
	}
	/**
	 * 获得当前应用目录名
	 * 
	 * @access public
	 * @return string
	 */
	public static function getAppDirName(){
		return self::$options['entry_maps'][$_GET['e']] ?? '';
	}
	/**
	 * 定义应用路径
	 * 
	 * @access public
	 * @return bool
	 */
	public static function setAppPath(){
		$appDirName = self::getAppDirName();
		if($appDirName){
			define('APP_PATH', UNIFIED_PATH . $appDirName . Z_DS);
			return true;
		}
		return false;
	}
}
