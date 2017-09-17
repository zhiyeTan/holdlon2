<?php
namespace z\core;

use z\lib\Basic;

/**
 * 硬盘（文件）缓存管理
 *
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 *
 */
class DiskCached{
	private static $staticCachePath; //静态缓存路径
	private static $dynamicCachePath; //静态缓存路径
	private function __construct(){} //不允许创建对象
	
	/**
	 * 获得缓存文件存放路径
	 * 
	 * @access private
	 * @param  int  $intCacheType  缓存类型
	 * @return path
	 */
	private static function getCachePath($intCacheType = CACHE_TYPE_STATIC){
		$filePath = $intCacheType == CACHE_TYPE_DYNAMIC ? self::$dynamicCachePath : self::$staticCachePath;
		if(!$filePath){
			$filePath = APP_PATH . ($intCacheType == CACHE_TYPE_DYNAMIC ? 'compiled' : 'cached') . Z_DS . $_GET['m'] . Z_DS;
			Basic::mkFolder($filePath);
			if($intCacheType == CACHE_TYPE_DYNAMIC){
				self::$dynamicCachePath = $filePath;
			}
			else{
				self::$staticCachePath = $filePath;
			}
		}
		return $filePath;
	}
	
	/**
	 * 获得动态缓存文件路径
	 * 
	 * @access public
	 * @param  string  $strFileName  文件名
	 * @return path/bool
	 */
	public static function getc($strFileName){
		$filePath = self::getCachePath(CACHE_TYPE_DYNAMIC) . $strFileName . '.php';
		if(Config::$options['php_cache_enable'] && is_file($filePath)){
			return $filePath;
		}
		return false;
	}
	
	/**
	 * 保存动态缓存文件，并返回文件路径
	 * 
	 * @access public
	 * @param  string  $strFileName  文件名
	 * @param  mixed   $nData        需要写入的数据
	 * @return path
	 */
	public static function savec($strFileName, $nData){
		$filePath = self::getCachePath(CACHE_TYPE_DYNAMIC) . $strFileName . '.php';
		Basic::write($filePath, $nData);
		return $filePath;
	}
	
	/**
	 * 获取缓存文件内容
	 * 
	 * @access public
	 * @param  string  $strFileName   文件名
	 * @param  int     $intCacheType  缓存类型
	 * @return string/path/bool
	 */
	public static function get($strFileName, $intCacheType = CACHE_TYPE_STATIC){
		$filePath = self::getCachePath(CACHE_TYPE_STATIC) . $strFileName;
		$expire = Config::$options[$intCacheType == CACHE_TYPE_DATA ? 'data_cache_expire' : 'html_cache_expire'];
		if($expire < 0){
			return false;
		}
		if($expire > 0 && time() > (filemtime($filePath) + $expire))
		{
			return false;
		}
		return Basic::read($filePath);
	}
	
	/**
	 * 保存内容到缓存文件
	 * 
	 * @access public
	 * @param  string  $strFileName  文件名
	 * @param  mixed   $nData        需要写入的数据
	 * @return bool
	 */
	public static function save($strFileName, $nData){
		$filePath = self::getCachePath(CACHE_TYPE_STATIC) . $strFileName;
		return Basic::write($filePath, $nData);
	}
}
