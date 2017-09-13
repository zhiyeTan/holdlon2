<?php

namespace z\lib;

/**
 * 文件系统管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Basic{
	/**
	 * 创建文件夹
	 * 
	 * @access  public
	 * @param   string  $pathFolder  目录路径
	 */
	public static function mkFolder($pathFolder){
		if(!is_dir($pathFolder)){
			@mkdir($pathFolder);
			@chmod($pathFolder, 0777);
		}
	}
	
	/**
	 * 读取文档内容
	 * 
	 * @access public
	 * @param  path    $pathFile        文件路径
	 * @param  bool    $boolSerialize   是否序列化的数据，默认true
	 * @return string/bool
	 */
	public static function read($pathFile, $boolSerialize = true) {
		$data = false;
		if(is_file($pathFile) && is_readable($pathFile)){
			$fp = fopen($pathFile, 'r');
			if(flock($fp, LOCK_SH)){
				$data = fread($fp, filesize($pathFile));
			}
			fclose($fp);
		}
		return $boolSerialize && $data ? unserialize($data) : $data;
	}
	
	/**
	 * 写入文档内容
	 * 
	 * @access public
	 * @param  path         $pathFile        文件路径
	 * @param  mixed        $nData           需要写入的数据
	 * @param  true/false   $boolSerialize   是否序列化的数据，默认true
	 * @param  true/false   $boolChange      是否变更内容，默认true
	 * @param  true/false   $boolCover       是否覆盖原内容（覆盖或追加），默认true
	 * @return boolean
	 */
	public static function write($pathFile, $nData, $boolSerialize = true, $boolChange = true, $boolCover = true){
		$bool = false;
		if(!$boolChange && is_file($pathFile)){
			$bool = true;
		}
		if(!$bool && (!is_file($pathFile) || is_writeable($pathFile))){
			$mode = $boolCover ? 'w' : 'ab';
			$file = fopen($pathFile, $mode);
			if(flock($file, LOCK_EX)){
				$bool = fputs($file, $boolSerialize ? serialize($nData) : $nData) ? true : false;
			}
			fclose($file);
		}
		return $bool;
	}
	
	/**
	 * 记录日志
	 * 
	 * @access public
	 * @param  string  $strFileName  日志文件名
	 * @param  string  $strContent   单条日志的内容
	 * @return boolean
	 */
	public static function logc($strFileName, $strContent){
		$withoutSuffixPath = APP_PATH . 'logs' . Z_DS . $strFileName;
		$logPath = $withoutSuffixPath . '.txt';
		// 如果文件存在且超过大小上限，则以当前时间重命名该文件
		if(is_file($logPath) && filesize($logPath) > LOG_MAX_SIZE){
			$newPath = $withoutSuffixPath . time() . '.txt';
			// 设置一个值，防止出现死循环(一次延迟100毫秒，30次相当于3s)
			$domax = 30;
			// 循环，直到成功或者超时
			$i = 0;
			do{
				++$i;
				if(!rename($logPath, $newPath)){
					usleep(100); // 延迟100毫秒
				}
			}
			while(!$status && $i < $domax);
		}
		// 保存日志
		return Basic::write($logPath, $strContent . PHP_EOL, false, true, false);
	}
	
	/**
	 * 递归处理目录
	 * @access  public
	 * @param   path      $pathTarget      目录路径
	 * @param   boolean   $boolDelOrList   处理方式（默认false删除，true获取文档树）
	 * @param   number    $intLevel        文档相对目录的层级
	 * @return  nothing/array
	 */
	public static function recursiveDealDir($pathTarget, $boolDelOrList = false, $intLevel = 0){
		$i = 0;
		$res = array();
		$fp = dir($pathTarget);
		while(false != ($item = $fp->read())){
			// 跳过.:
			if($item == '.' || $item == '..'){
				continue;
			}
			$tmpPath = $fp->path . Z_DS . $item;
			// 这部分是获取文档树用的
			if($boolDelOrList){
				$res[$i] = array(
					'name'	=> $item,
					'path'	=> $tmpPath,
					'type'	=> is_dir($tmpPath),
					'level'	=> $intLevel
				);
				if(is_dir($tmpPath)){
					$res[$i]['children'] = Basic::recursiveDealDir($tmpPath, $type, $intLevel + 1);
				}
				$i++;
			}
			// 这部分是执行删除操作
			else{
				if(is_dir($tmpPath)){
					@rmdir($tmpPath);
				}
				else{
					@unlink($tmpPath);
				}
			}
		}
		return $boolDelOrList ? $res : '';
	}
}