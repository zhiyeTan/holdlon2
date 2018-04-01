<?php
/**
 * 缓存类
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zModCacheCls
{
	/**
	 * 取得缓存
	 * @access public
	 * @param  string  $key     缓存键名
	 * @param  string  $type    储存类型(默认default)
	 * @param  int     $expire  有效时间(默认永久)
	 * @return string/array/bool
	 */
	public static function get($key, $type = 'default', $expire = 0){
		if($expire < 0){
			return false;
		}
		if(zCoreConfigCls::$options['redis_enable']){
			return zModRedisCls::init()->get($key, $type);
		}
		$filePath = ($type == 'appview' || $type == 'appdata' ? APP_CACHE_PATH : TMPFS_PATH . $type . Z_DS) . $key;
		if($expire > 0 && is_file($filePath) && time() > (filemtime($filePath) + $expire)){
			return false;
		}
		$data = zPubBasicLib::read($filePath);
		$jsonData = $data ? json_decode($data, true) : null;
		return $jsonData === null ? $data : $jsonData;
	}
	
	/**
	 * 保存缓存
	 * @access public
	 * @param  string        $key     缓存键名
	 * @param  array/string  $value   键值
	 * @param  string        $type    储存类型(默认default)
	 * @param  int           $expire  有效时间(默认永久)
	 * @return bool
	 */
	public static function save($key, $value, $type = 'default', $expire = 0){
		if($expire < 0){
			return false;
		}
		if(zCoreConfigCls::$options['redis_enable']){
			return zModRedisCls::init()->set($key, $value, $type, $expire);
		}
		$path = $type == 'appview' || $type == 'appdata' ? APP_CACHE_PATH : TMPFS_PATH . $type . Z_DS;
		zPubBasicLib::mkFolder($path);
		$value = is_array($value) ? json_encode($value) : $value;
		return zPubBasicLib::write($path . $key, $value, false);
	}
	
	/**
	 * 取得地址映射
	 * @access public
	 * @param  string  $key  短地址字符串
	 * @return string/bool
	 */
	public static function getUrlMap($key){
		return self::get($key, 'urlmap');
	}
	
	/**
	 * 保存地址映射
	 * @access public
	 * @param  string  $key    短地址字符串
	 * @param  string  $value  真实url地址
	 * @return bool
	 */
	public static function saveUrlMap($key, $value){
		return self::save($key, $value, 'urlmap');
	}
	
	/**
	 * 取得查询结果缓存
	 * @access public
	 * @param  string  $key     查询结果的缓存键名
	 * @param  int     $expire  有效时间
	 * @return string/bool
	 */
	public static function getSqlModel($key, $expire){
		return self::get($key, 'model', $expire);
	}
	
	/**
	 * 保存地址映射
	 * @access public
	 * @param  string        $key     查询结果的缓存键名
	 * @param  array/string  $value   真实url地址
	 * @param  int           $expire  有效时间
	 * @return bool
	 */
	public static function saveSqlModel($key, $value, $expire){
		return self::save($key, $value, 'model', $expire);
	}
	
	/**
	 * 取得应用的视图缓存
	 * @access public
	 * @param  string  $key  视图缓存键名
	 * @return string/bool
	 */
	public static function getAppViewCache($key){
		return self::get($key, 'appview', zCoreConfigCls::$options['view_cache_expire']);
	}
	
	/**
	 * 取得应用的接口缓存
	 * @access public
	 * @param  string  $key  接口缓存键名
	 * @return string/bool
	 */
	public static function getAppDataCache($key){
		return self::get($key, 'appdata', zCoreConfigCls::$options['data_cache_expire']);
	}
	
	/**
	 * 保存应用的视图缓存
	 * @access public
	 * @param  string        $key     视图缓存键名
	 * @param  array/string  $value   视图代码
	 * @return bool
	 */
	public static function saveAppViewCache($key, $value){
		return self::save($key, $value, 'appview', zCoreConfigCls::$options['view_cache_expire']);
	}
	
	/**
	 * 保存应用的接口缓存
	 * @access public
	 * @param  string        $key     接口缓存键名
	 * @param  array/string  $value   接口数据
	 * @return bool
	 */
	public static function saveAppDataCache($key, $value){
		return self::save($key, $value, 'appdata', zCoreConfigCls::$options['data_cache_expire']);
	}
}
