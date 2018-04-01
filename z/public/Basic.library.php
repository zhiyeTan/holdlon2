<?php
/**
 * 基本方法
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zPubBasicLib
{	
	/**
	 * 创建文件夹
	 * @access  public
	 * @param   string  $folderPath  目录路径
	 */
	public static function mkFolder($folderPath){
		if(!is_dir($folderPath)){
			@mkdir($folderPath, 0777, true);
		}
	}
	
	/**
	 * 读取文档内容
	 * @access public
	 * @param  path  $filePath  文件路径
	 * @return string/bool
	 */
	public static function read($filePath) {
		$data = false;
		if(is_file($filePath) && is_readable($filePath)){
			$fp = fopen($filePath, 'r');
			if(flock($fp, LOCK_SH)){
				$data = @fread($fp, filesize($filePath));
			}
			fclose($fp);
		}
		return $data;
	}
	
	/**
	 * 写入文档内容
	 * @access public
	 * @param  path        $filePath    文件路径
	 * @param  mixed       $content     需要写入的内容
	 * @param  true/false  $changeFlag  是否变更内容，默认true
	 * @param  true/false  $coverFlag   是否覆盖原内容（覆盖或追加），默认true
	 * @return boolean
	 */
	public static function write($filePath, $content, $changeFlag = true, $coverFlag = true){
		$bool = false;
		if(!$changeFlag && is_file($filePath)){
			$bool = true;
		}
		if(!$bool && (!is_file($filePath) || is_writeable($filePath))){
			$mode = $coverFlag ? 'w' : 'ab';
			$file = fopen($filePath, $mode);
			if(flock($file, LOCK_EX)){
				$bool = fputs($file, $content) ? true : false;
			}
			fclose($file);
		}
		return $bool;
	}
	
	/**
	 * 列出指定目录的结构树
	 * @access public
	 * @param  path   $targetPath   目录路径
	 * @param  array  $filterFiles  要过滤的文件数组
	 * @return array
	 */
	public static function listDirTree($targetPath, $filterFiles = []){
		$trees = self::recursiveDealDir($targetPath, true);
		return self::quickHandler($trees, 'children', [__CLASS__, 'filterDirInfo'], [$filterFiles, $targetPath]);
	}
	
	/**
	 * 删除指定目录下的所有文件
	 * @access public
	 * @param  path  $targetPath  目录路径
	 */
	public static function deleteDir($targetPath){
		return self::recursiveDealDir($targetPath);
	}
	
	/**
	 * 递归处理目录
	 * 由于list和delete由同一参数控制，对外开放具有风险，因此由另外语义明确的函数调用
	 * @access  private
	 * @param   path     $targetPath  目录路径
	 * @param   boolean  $deleteFlag  处理方式（默认false删除，true获取文档树）
	 * @param   number   $level       文档相对目录的层级
	 * @return  nothing/array
	 */
	private static function recursiveDealDir($targetPath, $deleteFlag = false, $level = 0){
		$i = 0;
		$res = [];
		$fp = dir($targetPath);
		while(false != ($item = $fp->read())){
			//跳过.:
			if($item == '.' || $item == '..'){
				continue;
			}
			$tmpPath = rtrim($fp->path, Z_DS) . Z_DS . $item;
			$type = is_dir($tmpPath);
			//这部分是获取文档树用的
			if($deleteFlag){
				$res[$i] = [
					'name'	=> $item,
					'path'	=> $tmpPath,
					'type'	=> $type,
					'level'	=> $level
				];
				if($type){
					$res[$i]['children'] = self::recursiveDealDir($tmpPath, $deleteFlag, $level + 1);
				}
				$i++;
			}
			//这部分是执行删除操作
			else{
				if($type){
					self::recursiveDealDir($tmpPath, false);
					@rmdir($tmpPath);//TODO 目录必须是空的
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
	 * @access private
	 * @param  array  $dirInfo      目录结构信息
	 * @param  array  $filterFiles  要过滤的文件数组
	 * @param  path   $baseDir      基准路径
	 * @param  int    $hiddenPath   是否隐藏物理路径
	 * @return array
	 */
	private static function filterDirInfo($dirInfo, $filterFiles, $baseDir, $hiddenPath = true){
		if(in_array($dirInfo['name'], $filterFiles)){
			return false;
		}
		$dirInfo['link'] = str_replace(Z_DS, '/', str_replace($baseDir, '', $dirInfo['path']));
		if($hiddenPath){
			unset($dirInfo['path']);
		}
		return $dirInfo;
	}
	
	/**
	 * 快速处理数组
	 * @access public
	 * @param  array   $arrTarget    目标数组
	 * @param  string  $key          多维子数组的键名
	 * @param  array   $funCallBack  回调函数(调用类方法时以数组形式传参，eg:[__CLASS__, 'method'])
	 * @param  array   $funParam     回调函数的参数(不包含目标数组，且回调函数的目标数组必须是第一个参数)
	 * @param  int     $thread       线程数
	 * @return array
	 */
	public static function quickHandler($arrTarget, $key, $funCallBack, $funParam, $thread = 5){
		if(empty($arrTarget)){
			return [];
		}
		$result = [];
		$size = ceil(count($arrTarget) / $thread);
		$chunks = array_chunk($arrTarget, $size);
		for($i = 0; $i < $size; $i++){
			for($j = 0; $j < $thread; $j++){
				if(!empty($chunks[$j][$i])){
					$tmpParam = $funParam;
					array_unshift($tmpParam, $chunks[$j][$i]);
					$tmpArr = call_user_func_array($funCallBack, $tmpParam);
					if(!empty($tmpArr[$key])){
						$tmpArr[$key] = self::quickHandler($chunks[$j][$i][$key], $key, $funCallBack, $funParam, $thread);
					}
					if($tmpArr){
						$result[] = $tmpArr;
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * 转为时间戳
	 * @access public
	 * @param  string  $date  时间日期
	 * @return int
	 */
	public static function toTimestamp($date){
		return $date !== intval($date) ? strtotime($date) : $date;
	}
	
	/**
	 * 根据时间格式获取对应的值
	 * @access public
	 * @param  string  $format       格式(默认'Y')
	 * @param  mixed   $dateOrTimes  日期时间/时间戳
	 * @param  bool    $isTimeStamp  参数是否为时间戳(默认true)
	 * @return string
	 */
	public static function date($format = '', $dateOrTimes = '', $isTimeStamp = true){
		if(!$format){
			$format = 'Y';
		}
		if($dateOrTimes && !$isTimeStamp){
			$dateOrTimes = strtotime($dateOrTimes);
		}
		if(!$dateOrTimes){
			$dateOrTimes = time();
		}
		return date($format, $dateOrTimes);
	}
}