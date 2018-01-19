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
	 * @param  path    $pathTarget   目录路径
	 * @param  array   $arrFiles     要过滤的文件数组
	 * @return array
	 */
	public static function listDirTree($pathTarget, $arrFiles = []){
		$trees = self::recursiveDealDir($pathTarget, true);
		return self::quickHandler($trees, 'children', [__CLASS__, 'filterDirInfo'], [$arrFiles, $pathTarget]);
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
		$res = [];
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
				$res[$i] = [
					'name'	=> $item,
					'path'	=> $tmpPath,
					'type'	=> $type,
					'level'	=> $intLevel
				];
				if($type){
					$res[$i]['children'] = self::recursiveDealDir($tmpPath, $boolDelOrList, $intLevel + 1);
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
	
	/**
	 * 递归过滤掉目录树的指定信息
	 * 
	 * @access private
	 * @param  array   $arrDirInfo     目录结构信息
	 * @param  array   $arrFiles       要过滤的文件数组
	 * @param  string  $pathBaseDir    基准路径
	 * @param  int     $intHiddenPath  是否隐藏物理路径
	 * @return array
	 */
	private static function filterDirInfo($arrDirInfo, $arrFiles, $pathBaseDir, $intHiddenPath = true){
		if(in_array($arrDirInfo['name'], $arrFiles)){
			return false;
		}
		$arrDirInfo['link'] = str_replace(Z_DS, '/', str_replace($pathBaseDir, '', $arrDirInfo['path']));
		if($intHiddenPath){
			unset($arrDirInfo['path']);
		}
		return $arrDirInfo;
	}
	
	/**
	 * 快速处理数组
	 * 
	 * @access public
	 * @param  array   $arrTarget    目标数组
	 * @param  string  $strKey       多维子数组的键名
	 * @param  array   $funCallBack  回调函数(调用类方法时以数组形式传参，eg:[__CLASS__, 'method'])
	 * @param  array   $arrParam     回调函数的参数(不包含目标数组，且回调函数的目标数组必须是第一个参数)
	 * @param  int     $intThread    线程数
	 * @return array
	 */
	public static function quickHandler($arrTarget, $strKey, $funCallBack, $arrParam, $intThread = 5){
		$size = ceil(count($arrTarget) / $intThread);
		$chunks = array_chunk($arrTarget, $size);
		$result = [];
		for($i = 0; $i < $size; $i++){
			for($j = 0; $j < $intThread; $j++){
				if(!empty($chunks[$j][$i])){
					$tmpParam = $arrParam;
					array_unshift($tmpParam, $chunks[$j][$i]);
					$tmpArr = call_user_func_array($funCallBack, $tmpParam);
					if(!empty($tmpArr[$strKey])){
						$tmpArr[$strKey] = self::quickHandler($chunks[$j][$i][$strKey], $strKey, $funCallBack, $arrParam, $intThread);
					}
					if(!empty($tmpArr)){
						$result[] = $tmpArr;
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * 转为时间戳
	 * 
	 * @access public
	 * @param  string  $date  时间日期
	 * @return int
	 */
	public static function toTimestamp($date){
		return $date !== intval($date) ? strtotime($date) : $date;
	}
	
	/**
	 * 根据时间格式获取对应的值
	 * 
	 * @access public
	 * @param  string  $format           格式(默认'Y')
	 * @param  mixed   $nTime            日期时间/时间戳
	 * @param  bool    $boolIsTimeStamp  参数是否为时间戳(默认true)
	 * @return string
	 */
	public static function zDate($format = '', $nTime = '', $boolIsTimeStamp = true){
		if(!$format){
			$format = 'Y';
		}
		if($nTime && !$boolIsTimeStamp){
			$nTime = strtotime($nTime);
		}
		if(!$nTime){
			$nTime = time();
		}
		return date($format, $nTime);
	}
}