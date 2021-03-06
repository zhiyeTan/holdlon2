<?php
namespace z\core;

use z\lib\Basic;

/**
 * 本地缓存文件服务
 * Local cache file service
 *
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 *
 */
class Locafis
{
	private static $staticCachePath;//静态缓存路径
	private static $dynamicCachePath;//静态缓存路径
	private function __construct(){}//静态类，禁止构造对象
	
	/**
	 * 获得缓存文件存放路径
	 * 
	 * @access private
	 * @param  int  $intCacheType  缓存类型
	 * @return path
	 */
	private static function getCachePath($intCacheType){
		$filePath = $intCacheType == CACHE_TYPE_DYNAMIC ? self::$dynamicCachePath : self::$staticCachePath;
		if(!$filePath){
			$filePath = Config::getLocafisPath($intCacheType);
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
	 * 获得缓存文件名
	 * 
	 * @access private
	 * @param  int  $intCacheType  缓存类型
	 * @return string
	 */
	private static function getCacheFileName($intCacheType, $strFileName = ''){
		if(!$strFileName){
			$strFileName = Config::getCacheFileName($intCacheType);
		}
		return $strFileName;
	}
	
	/**
	 * 获得动态缓存文件路径
	 * 
	 * @access public
	 * @param  string  $strFileName  文件名
	 * @return path/bool
	 */
	public static function getc($strFileName = ''){
		$strFileName = self::getCacheFileName(CACHE_TYPE_DYNAMIC, $strFileName);
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
	 * @param  mixed   $nData        需要写入的数据
	 * @param  string  $strFileName  文件名
	 * @return path
	 */
	public static function savec($nData, $strFileName = ''){
		$strFileName = self::getCacheFileName(CACHE_TYPE_DYNAMIC, $strFileName);
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
	public static function get($intCacheType = CACHE_TYPE_STATIC, $strFileName = ''){
		$strFileName = self::getCacheFileName(CACHE_TYPE_STATIC, $strFileName);
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
	public static function save($nData, $strFileName = ''){
		$strFileName = self::getCacheFileName(CACHE_TYPE_STATIC, $strFileName);
		$filePath = self::getCachePath(CACHE_TYPE_STATIC) . $strFileName;
		return Basic::write($filePath, $nData);
	}
}
