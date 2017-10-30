<?php

namespace z\lib;

/**
 * 基本方法
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Basic
{
	/**
	 * 创建文件夹
	 * 
	 * @access  public
	 * @param   string  $pathFolder  目录路径
	 */
	public static function mkFolder($pathFolder){
		if(!is_dir($pathFolder)){
			@mkdir($pathFolder, 0777, true);
		}
	}
	
	/**
	 * 读取文档内容
	 * 
	 * @access public
	 * @param  path    $pathFile        文件路径
	 * @return string/bool
	 */
	public static function read($pathFile) {
		$data = false;
		if(is_file($pathFile) && is_readable($pathFile)){
			$fp = fopen($pathFile, 'r');
			if(flock($fp, LOCK_SH)){
				$data = fread($fp, filesize($pathFile));
			}
			fclose($fp);
		}
		return $data;
	}
	
	/**
	 * 写入文档内容
	 * 
	 * @access public
	 * @param  path         $pathFile        文件路径
	 * @param  mixed        $nData           需要写入的数据
	 * @param  true/false   $boolChange      是否变更内容，默认true
	 * @param  true/false   $boolCover       是否覆盖原内容（覆盖或追加），默认true
	 * @return boolean
	 */
	public static function write($pathFile, $nData, $boolChange = true, $boolCover = true){
		$bool = false;
		if(!$boolChange && is_file($pathFile)){
			$bool = true;
		}
		if(!$bool && (!is_file($pathFile) || is_writeable($pathFile))){
			$mode = $boolCover ? 'w' : 'ab';
			$file = fopen($pathFile, $mode);
			if(flock($file, LOCK_EX)){
				$bool = fputs($file, $nData) ? true : false;
			}
			fclose($file);
		}
		return $bool;
	}
	
	/**
	 * 列出指定目录的结构树
	 * 
	 * @access public
	 * @param  path    $pathTarget    目录路径
	 * @return array
	 */
	public static function listDirTree($pathTarget){
		return self::recursiveDealDir($pathTarget, true);
	}
	
	/**
	 * 删除指定目录下的所有文件
	 * 
	 * @access public
	 * @param  path    $pathTarget    目录路径
	 */
	public static function deleteDir($pathTarget){
		return self::recursiveDealDir($pathTarget);
	}
	
	/**
	 * 递归处理目录
	 * 由于list和delete由同一参数控制，对外开放具有风险，因此由另外语义明确的函数调用
	 * 
	 * @access  private
	 * @param   path      $pathTarget      目录路径
	 * @param   boolean   $boolDelOrList   处理方式（默认false删除，true获取文档树）
	 * @param   number    $intLevel        文档相对目录的层级
	 * @return  nothing/array
	 */
	private static function recursiveDealDir($pathTarget, $boolDelOrList = false, $intLevel = 0){
		$i = 0;
		$res = array();
		$fp = dir($pathTarget);
		while(false != ($item = $fp->read())){
			// 跳过.:
			if($item == '.' || $item == '..'){
				continue;
			}
			$tmpPath = rtrim($fp->path, Z_DS) . Z_DS . $item;
			$type = is_dir($tmpPath);
			// 这部分是获取文档树用的
			if($boolDelOrList){
				$res[$i] = array(
					'name'	=> $item,
					'path'	=> $tmpPath,
					'type'	=> $type,
					'level'	=> $intLevel
				);
				if($type){
					$res[$i]['children'] = Basic::recursiveDealDir($tmpPath, $boolDelOrList, $intLevel + 1);
				}
				$i++;
			}
			// 这部分是执行删除操作
			else{
				if($type){
					@rmdir($tmpPath);
				}
				else{
					@unlink($tmpPath);
				}
			}
		}
		return $res;
	}
}