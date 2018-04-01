<?php
/**
 * 配置管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreConfigCls
{
	public static $options = [];//配置项
	private static $_instance;//实例
	//私有构造方法
	private function __construct(){
		error_reporting(E_ALL);
		$path = UNIFIED_PATH . 'z' . Z_DS;
		self::$options = require $path . 'config' . Z_DS . 'config.php';//加载默认配置项
		require $path . 'define' . Z_DS . 'define.php';//加载预定义常量
		//定义恒定路径常量
		define('TMPFS_PATH', UNIFIED_PATH . 'tmpfs' . Z_DS);//作为虚拟文件系统的路径
		define('COMPONENT_PATH', UNIFIED_PATH . 'component' . Z_DS);//组件所在路径
		define('COMPONENT_COMPILE_PATH', TMPFS_PATH . 'compile' . Z_DS . 'component' . Z_DS);//组件编译后的路径
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
	 * 配置应用的基本信息
	 * @access public
	 */
	public static function configure(){
		//定义与应用or模块相关的常量
		define('APP_DIR', $_GET['e'] == 'index' ? APP_DEFAULT_DIR : $_GET['e']);//目录名
		define('APP_PATH', UNIFIED_PATH . 'app' . Z_DS . APP_DIR . Z_DS);//目录路径
		define('APP_LOG_PATH', UNIFIED_PATH . 'log' . Z_DS . APP_DIR . Z_DS);//日志路径
		define('APP_CACHE_PATH', TMPFS_PATH . 'cache' . Z_DS . APP_DIR . Z_DS);//静态缓存路径
		define('APP_COMPILE_PATH', TMPFS_PATH . 'compile' . Z_DS . APP_DIR . Z_DS);//动态编译路径
		//定义视图相关路径
		define('PUBLIC_VIEW_PATH', UNIFIED_PATH . 'app' . Z_DS . 'view' . Z_DS);//公共视图所在路径
		define('VIEW_FILE_PATH', APP_PATH . 'view' . Z_DS . $_GET['c'] . TEMPLATE_SUFFIX);
		define('VIEW_COMPILED_FILE_PATH', APP_COMPILE_PATH . $_GET['c'] . '.php');
		//加载应用or模块的独有配置项
		$appConfigFile = APP_PATH . 'config' . Z_DS . 'config.php';
		if(is_file($appConfigFile)){
			self::$options = array_merge(self::$options, include $appConfigFile);
		}
		//修正静态资源相关设置
		self::$options['static_domain'] = rtrim(self::$options['static_domain'], '/') . '/';
		self::$options['static_suffix'] = trim(self::$options['static_suffix'], '|');
		//设定时区
		date_default_timezone_set(self::$options['default_timezone']);
	}
	
	/**
	 * 加载配置文件
	 * @access public
	 * @param  string  $fileName  配置文件名
	 * @return array
	 */
	public static function loadConfig($fileName){
		$cfg = require UNIFIED_PATH . 'z' . Z_DS . 'config' . Z_DS . $fileName . '.php';
		$appCfgFile = APP_PATH . 'config' . Z_DS . $fileName .'.php';
		if(is_file($appCfgFile)){
			$cfg = require $appCfgFile;
		}
		return $cfg;
	}
	
	/**
	 * 加载常量文件
	 * @access private
	 * @param  string  $fileName  文件名
	 * @param  bool    $isAppFile  是否为应用/模块的常量文件
	 */
	private static function loadDefineFile($fileName, $isAppFile){
		$filePath = UNIFIED_PATH . ($isAppFile ? 'app' : 'z') . Z_DS . 'define' . Z_DS . $fileName . '.php';
		if(is_file($filePath)){
			require $filePath;
		}
	}
	
	/**
	 * 加载应用/模块的常量文件
	 * @access public
	 * @param  string  $fileName  文件名
	 */
	public static function loadAppDefine($fileName){
		self::loadDefineFile($fileName, true);
	}
	
	/**
	 * 加载框架的常量文件
	 * @access public
	 * @param  string  $fileName  文件名
	 */
	public static function loadFrameDefine($fileName){
		self::loadDefineFile($fileName, false);
	}
}
