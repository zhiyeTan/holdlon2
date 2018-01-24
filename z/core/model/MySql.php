<?php
namespace z\core\model;

use Redis;
use z\lib\Basic;

/**
 * 远程字典服务器集群
 * Remote dictionary server cluster
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class MySql extends Database
{
	private static $tagName 	= 'mysql';
	private static $useExplain	= true;
	// 查询耗时，如果超过这个时间则考虑写入慢查询日志中
	private static $limitTime	= 1;
	private static $_instance;//类实例
	//私有的构造函数
	private function __construct(){
		Connector::init()->setConfig(self::$tagName);
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
	 * mysql读操作
	 * 
	 * @access protected
	 * @param  string  $strSql   查询语句
	 * @param  string  $strType  库存储类型
	 * @return resource
	 */
	protected static function read($strSql, $strType){
		$conn = Connector::init()->connect(self::$tagName, $strType, false);
		$result = false;
		if($conn){
			$startTimes = microtime(true);
			$result = $conn->query($strSql);
			$usedTime = sprintf('%.5f', microtime(true) - $startTime);
			//开启查询分析，且执行时间超过设定时长
			if(self::$useExplain && $usedTime > self::$limitTime){
				$esql = 'EXPLAIN ' . $strSql;
				$explain = $conn->query($esql);
				if($explain !== false){
					$row = $explain->fetch_assoc();
					//分析查询语句，把全表扫描库存储类型的查询信息记录到慢查询日志中
					//注：index和all都是全表扫描，区别是index从索引中读取，all从硬盘中读取
					if(isset($row['type']) && in_array(strtolower($row['type']), ['index', 'all'])){
				    	$content  = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ';
						$content .= 'times:' . $usedTime . ' ';
						$content .= $row['type'] . ' ';
						$content .= $row['key'] . ' ';
						$content .= $strSql;
						Log::save('slowQueryLog', $content);
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * 获取一个数据
	 * 
	 * @access public
	 * @param  string  $strSql   查询语句
	 * @param  string  $strType  库存储类型
	 * @return string/boolean
	 */
	public static function getOne($strSql, $strType = 'default'){
		$result = self::read($strSql, $strType);
		if($result !== false){
			$row = $result->fetch_row();
			return $row !== false ? $row[0] : '';
		}
		return false;
	}
	
	/**
	 * 获取一列数据
	 * 
	 * @access public
	 * @param  string  $strSql   查询语句
	 * @param  string  $strType  库存储类型
	 * @return array/boolean
	 */
	public static function getCol($strSql, $strType = 'default'){
		$result = self::read($strSql, $strType);
		if($result !== false){
			$col = [];
			while($row = $result->fetch_row()){
				$col[] = $row[0];
			}
			return $col;
		}
		return false;
	}
	
	/**
	 * 获取一行数据
	 * 
	 * @access public
	 * @param  string  $strSql   查询语句
	 * @param  string  $strType  库存储类型
	 * @return array/boolean
	 */
	public static function getRow($strSql, $strType = 'default'){
		$result = self::read($strSql, $strType);
		if($result !== false){
			return $result->fetch_assoc();
		}
		return false;
	}
	
	/**
	 * 获取全部数据
	 * 
	 * @access public
	 * @param  string  $strSql   查询语句
	 * @param  string  $strType  库存储类型
	 * @return array/boolean
	 */
	public static function getAll($strSql, $strType = 'default'){
		$result = self::read($strSql, $strType);
		if($result !== false){
			$all = [];
			while($row = $result->fetch_assoc()){
				$all[] = $row;
			}
			return $all;
		}
		return false;
	}
	
	/**
	 * 执行插入操作
	 * 
	 * @access public
	 * @param  string  $strSql   查询语句
	 * @param  string  $strType  库存储类型
	 * @return insert_id/false
	 */
	public static function insert($strSql, $strType){
		$conn = Connector::init()->connect(self::$tagName, $strType);
		if($conn){
			if($conn->query($strSql)){
				return $conn->insert_id;
			}
		}
		return false;
	}
	
	/**
	 * 执行更新操作
	 * 
	 * @access public
	 * @param  string  $strSql   查询语句
	 * @param  string  $strType  库存储类型
	 * @return boolean
	 */
	public static function update($strSql, $strType){
		$conn = Connector::init()->connect(self::$tagName, $strType);
		if($conn){
			return $conn->query($strSql);
		}
		return false;
	}
	
	/**
	 * 执行删除操作
	 * 
	 * @access public
	 * @param  string  $strSql   查询语句
	 * @param  string  $strType  库存储类型
	 * @return boolean
	 */
	public static function delete($strSql, $strType){
		$conn = Connector::init()->connect(self::$tagName, $strType);
		if($conn){
			return $conn->query($strSql);
		}
		return false;
	}
	
}
