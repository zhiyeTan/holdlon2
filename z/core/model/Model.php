<?php

namespace z\core\model;

use z\core\Config;

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
	 * @param  string  $strType        查询类型(CURD)
	 * @param  int     $intReturnType  查询结果的返回类型
	 * @return class
	 */
	private static function query($strType, $intReturnType = ''){
		$sql = self::sql($strType);
		if($strType == 'select' && self::$useRedis){
			$cacheKey = md5($sql);
			$cacheData = Redisc::init()->get($cacheKey, self::$modelType);
			if($cacheData){
				return $cacheData;
			}
		}
		//穿透到mysql
		switch($strType){
			case 'insert':
				$result = MySql::init()->insert($sql, self::$dbModelType);
				break;
			case 'update':
				$result = MySql::init()->update($sql, self::$dbModelType);
				break;
			case 'delete':
				$result = MySql::init()->delete($sql, self::$dbModelType);
				break;
			default:
				//$result = MySql::init()->update($sql, self::$dbModelType);
		}
	}
	
	/**
	 * 获取一个数据
	 * 
	 * @access public
	 * @return string/boolean
	 */
	public static function getOne(){
		return self::query('select', RETURN_QUERY_RESULT_ONE);
	}
	
	/**
	 * 获取一列数据
	 * 
	 * @access public
	 * @return array/boolean
	 */
	public static function getCol(){
		return self::query('select', RETURN_QUERY_RESULT_COL);
	}
	
	/**
	 * 获取一行数据
	 * 
	 * @access public
	 * @return array/boolean
	 */
	public static function getRow(){
		return self::query('select', RETURN_QUERY_RESULT_ROW);
	}
	
	/**
	 * 获取全部数据
	 * 
	 * @access public
	 * @return array/boolean
	 */
	public static function getAll(){
		return self::query('select', RETURN_QUERY_RESULT_ALL);
	}
	
	/**
	 * 执行插入操作
	 */
	public static function insert(){
		return self::query('insert');
	}
	
	/**
	 * 执行更新操作
	 */
	public static function update(){
		return self::query('update');
	}
	
	/**
	 * 执行删除操作
	 */
	public static function delete(){
		return self::query('delete');
	}
}
