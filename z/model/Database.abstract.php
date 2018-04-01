<?php
/**
 * 数据库抽象类
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
abstract class zModDatabaseAbs
{
	/**
	 * 数据库读操作
	 * @access protected
	 * @param  string  $sql   查询语句
	 * @param  string  $type  库存储类型
	 * @return resource
	 */
	abstract protected static function read($sql, $type);
	
	/**
	 * 获取一个数据
	 * @access public
	 * @param  string  $sql   查询语句
	 * @param  string  $type  库存储类型
	 * @return string/boolean
	 */
	abstract public static function getOne($sql, $type);
	
	/**
	 * 获取一列数据
	 * @access public
	 * @param  string  $sql   查询语句
	 * @param  string  $type  库存储类型
	 * @return array/boolean
	 */
	abstract public static function getCol($sql, $type);
	
	/**
	 * 获取一行数据
	 * @access public
	 * @param  string  $sql   查询语句
	 * @param  string  $type  库存储类型
	 * @return array/boolean
	 */
	abstract public static function getRow($sql, $type);
	
	/**
	 * 获取全部数据
	 * @access public
	 * @param  string  $sql   查询语句
	 * @param  string  $type  库存储类型
	 * @return array/boolean
	 */
	abstract public static function getAll($sql, $type);
	
	/**
	 * 执行插入操作
	 * @access public
	 * @param  string  $sql   查询语句
	 * @param  string  $type  库存储类型
	 * @return insert_id/false
	 */
	abstract public static function insert($sql, $type);
	
	/**
	 * 执行更新操作
	 * @access public
	 * @param  string  $sql   查询语句
	 * @param  string  $type  库存储类型
	 * @return boolean
	 */
	abstract public static function update($sql, $type);
	
	/**
	 * 执行删除操作
	 * @access public
	 * @param  string  $sql   查询语句
	 * @param  string  $type  库存储类型
	 * @return boolean
	 */
	abstract public static function delete($sql, $type);
}
