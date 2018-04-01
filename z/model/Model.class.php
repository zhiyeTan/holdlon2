<?php
/**
 * 数据模型类
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zModModelCls
{
	use zModSqlBuilderTra;
	
	private static $_instance;
	
	//禁止直接创建对象
	private function __construct(){
		self::$dbRules = zCoreConfigCls::loadConfig('dbRules');
	}
	
	/**
	 * 单例构造方法
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
	 * @access private
	 * @param  string  $type    查询类型(CURD)
	 * @param  int     $method  查询处理的方法名
	 * @return class
	 */
	private static function query($type, $method = ''){
		$sql = self::sql($type);
		$isRead = $type == 'select';
		if($isRead){
			//强制使用缓存处理时，有限时间设为永久
			$expire = self::$mandatory ? 0 : zCoreConfigCls::$options['model_cache_expire'];
			//键名采用dbname-tablename-md5($sql)格式，以便在需要时根据库和表更新缓存
			$cacheKey = self::$realDb . '-' . self::$realTable . '-' . md5($sql);
			$cacheData = zModCacheCls::getSqlModel($cacheKey, $expire);
			if($cacheData){
				self::clean();
				return $cacheData;
			}
		}
		//穿透到mysql
		$methodName = $type;
		if($isRead){
			$methodName = $method;
		}
		$result = zModMySqlCls::init()->$methodName($sql, self::$dbModelType);
		//缓存数据
		if($isRead){
			zModCacheCls::saveSqlModel($cacheKey, $result, $expire);
		}
		self::clean();
		return $result;
	}
	
	/**
	 * 获取一个数据
	 * @access public
	 * @return string/boolean
	 */
	public static function getOne(){
		return self::query('select', 'getOne');
	}
	
	/**
	 * 获取一列数据
	 * @access public
	 * @return array/boolean
	 */
	public static function getCol(){
		return self::query('select', 'getCol');
	}
	
	/**
	 * 获取一行数据
	 * @access public
	 * @return array/boolean
	 */
	public static function getRow(){
		return self::query('select', 'getRow');
	}
	
	/**
	 * 获取全部数据
	 * @access public
	 * @return array/boolean
	 */
	public static function getAll(){
		return self::query('select', 'getAll');
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
