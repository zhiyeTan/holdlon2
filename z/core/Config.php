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
	public static $options = [];//配置项
	private static $_instance;//实例
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
	
	/**
	 * 是否使用静态缓存
	 * 
	 * @access public
	 * @param  int  $intCacheType  缓存类型（数据、静态HTML）
	 * @return bool
	 */
	public static function whetherUseStaticCache($intCacheType){
		if($intCacheType == CACHE_TYPE_DATA){
			return self::$options['data_cache_expire'] >= 0;
		}
		return self::$options['html_cache_expire'] >= 0;
	}
	
	/**
	 * 获得tmpfs下对应的应用目录路径
	 * 
	 * @access public
	 * @param  string  $strTmpfsTypeName  tmpfs类型名（文件夹名）
	 * @param  bool    $boolSetAppDir     是否设置应用目录，默认true
	 * @return path
	 */
	public static function getAppPathByTmpfs($strTmpfsTypeName, $boolSetAppDir = true){
		$path = UNIFIED_PATH . 'tmpfs' . Z_DS . $strTmpfsTypeName . Z_DS;
		if($boolSetAppDir){
			$path .= self::getAppDirName() . Z_DS;
		}
		return $path;
	}
	
	/**
	 * 获得本地缓存文件服务所在位置目录
	 * 
	 * @access public
	 * @param  int  $intCacheType  缓存类型
	 * @return path
	 */
	public static function getLocafisPath($intCacheType){
		$Path  = self::getAppPathByTmpfs(TMPFS_CACHE_DIR);
		$Path .= ($intCacheType == CACHE_TYPE_DYNAMIC ? 'compiled' : 'cached') . Z_DS . $_GET['m'] . Z_DS;
		return $Path;
	}
	
	/**
	 * 获得缓存文件名
	 * 
	 * @access public
	 * @param  int  $intCacheType  缓存类型
	 * @return string
	 */
	public static function getCacheFileName($intCacheType, $arrUrlParam = ''){
		$arrUrlParam = $arrUrlParam ?: $_GET;
		self::correctBasicUrlParamArray($arrUrlParam);
		if($intCacheType == CACHE_TYPE_DYNAMIC){
			$tmp = array(
				'm'=>$arrUrlParam['m'],
				'e'=>$arrUrlParam['e'],
				'c'=>$arrUrlParam['c']
			);
		}
		else{
			$tmp = $arrUrlParam;
		}
		return http_build_query($tmp);
	}
	
	/**
	 * 修正基本的URL参数数组
	 * 
	 * @access public
	 * @param  array    $arrTarget   要修正的数组
	 */
	public static function correctBasicUrlParamArray(&$arrTarget){
		$arrTarget['m'] = $arrTarget['m'] ?? 'index';
		$arrTarget['e'] = $arrTarget['e'] ?? 'index';
		$arrTarget['c'] = $arrTarget['c'] ?? 'index';
	}
	
	/**
	 * 获得控制器路径
	 * @access public
	 * @param  string  $strControllerName  控制器名
	 * @param  string  $strModuleName      模块名
	 * @param  string  $strEntryName       入口名
	 * @return path
	 */
	public static function getControllerPath($strControllerName = '', $strModuleName = '', $strEntryName = ''){
		$path = $strEntryName ? (UNIFIED_PATH . $strEntryName . Z_DS) : APP_PATH;
		$strModuleName = $strModuleName ?: $_GET['m'];
		$strControllerName = $strControllerName ?: $_GET['c'];
		return $path . 'controllers' . Z_DS . $strModuleName . Z_DS . $strControllerName . '.php';
	}
	
	/**
	 * 获得控制器别名
	 * 
	 * @access public
	 * @param  string  $strModuleName      模块名
	 * @param  string  $strEntryName       入口名
	 * @return string
	 */
	public static function getControllerAlias($strControllerName = '', $strModuleName = ''){
		$strModuleName = $strModuleName ?: $_GET['m'];
		$strControllerName = $strControllerName ?: $_GET['c'];
		return '\\controllers\\' . $strModuleName . '\\' . $strControllerName;
	}
	
	/**
	 * 获得视图文件路径
	 * 
	 * @access public
	 * @param  string  $strViewName  视图名
	 * @return path
	 */
	public static function getViewPath($strViewName){
		return APP_PATH . 'views' . Z_DS . $_GET['m'] . Z_DS . $strViewName . TEMPLATE_SUFFIX;
	}
	
	/**
	 * 获得部件文件路径
	 * 
	 * @access public
	 * @param  string  $strWidgetName  部件名
	 * @return path
	 */
	public static function getWidgetPath($strWidgetName){
		return APP_PATH . 'widget' . Z_DS . $strWidgetName . WIDGET_SUFFIX;
	}
	
}
