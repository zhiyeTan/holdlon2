<?php

namespace z\core\model;

use z\core\{
	Config,
	Redisc
};

/**
 * 数据模型类
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class Model
{
	use SqlBuilder;
	private static $useRedis = true;
	private static $_instance;
	//禁止直接创建对象
	private function __construct(){
		Config::loadFrameFile('dbDefine', 'define');
		self::$dbRules = Config::loadConfig('dbRules');
	}
	
	/**
	 * 单例构造方法
	 * 
	 * @access public
	 * @return class
	 */
	public static function init(){
		if(!self::$_instance){
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	/**
	 * 查询
	 * 
	 * @access private
	 * @param  string  $type  查询类型(CURD)
	 * @return class
	 */
	private static function query($type){
		$sql = self::sql($type);
		if($type == 'select'){
			$cacheKey = md5($sql);
			if(self::$useRedis){
				$cacheData = Redisc::init()->get($cacheKey, self::$modelType);
				if($cacheData){
					return $cacheData;
				}
			}
		}
		
		
	}
}
