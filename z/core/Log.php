<?php
namespace z\core;

use z\lib\Basic;

class Log
{
	private static $logsPath; //日志路径
	private function __construct(){} //禁止实例化
	/**
	 * 设置当前应用对应的日志目录路径
	 * 如不存在则创建
	 * 
	 * @access private
	 */
	private static function setLogsPath(){
		self::$logsPath = UNIFIED_LOG_PATH . Config::getAppDirName() . Z_DS;
		Basic::mkFolder(self::$logsPath);
	}
	/**
	 * 获取日志路径
	 * 
	 * @access private
	 * @return path
	 */
	private static function getLogsPath(){
		if(!self::$logsPath){
			self::setLogsPath();
		}
		return self::$logsPath;
	}
	/**
	 * 保存一条日志
	 * 
	 * @access public
	 * @param  string  $strFileName             日志文件名
	 * @param  string  $magicVal                当使用第三个参数时，作为分析型日志的key，否则作为记录型日志的内容
	 * @param  string  $strAnalysisTypeContent  分析型日志要保存的内容
	 * @return boolean
	 */
	public static function save($strFileName, $magicVal, $strAnalysisTypeContent = ''){
		if($strAnalysisTypeContent){
			self::saveAnalysisTypeLog($strFileName, $magicVal, $strAnalysisTypeContent);
		}
		else{
			self::saveRecordTypeLog($strFileName, $magicVal);
		}
	}
	/**
	 * 保存记录型的日志
	 * 
	 * @access private
	 * @param  string  $strFileName  日志文件名
	 * @param  string  $strContent   单条日志的内容
	 * @return boolean
	 */
	private static function saveRecordTypeLog($strFileName, $strContent){
		$withoutSuffixPath = self::getLogsPath() . $strFileName;
		$logPath = $withoutSuffixPath . '.txt';
		//如果文件存在且超过大小上限，则以当前时间重命名该文件
		if(is_file($logPath) && filesize($logPath) > LOG_MAX_SIZE){
			$newPath = $withoutSuffixPath . time() . '.txt';
			//设置一个值，防止出现死循环(一次延迟100毫秒，30次相当于3s)
			$domax = 30;
			//循环，直到成功或者超时
			$i = 0;
			do{
				++$i;
				if(!rename($logPath, $newPath)){
					usleep(100); //延迟100毫秒
				}
			}
			while(!$status && $i < $domax);
		}
		Basic::write($logPath, $strContent . PHP_EOL, false, true, false);
	}
	/**
	 * 保存分析型的日志
	 * 保存新日志时自动在该行后面加上[1]
	 * 保存一条已存在的记录时，则自动对对应行后面的数字累加并保存
	 * 
	 * @access private
	 * @param  string  $strFileName  日志文件名
	 * @param  string  $strKey       作为识别键用的唯一字符串
	 * @param  string  $strContent   内容
	 */
	private static function saveAnalysisTypeLog($strFileName, $strKey, $strContent){
		$logPath = self::getLogsPath() . $strFileName . '.txt';
		$logContent = Basic::read($logPath, false);
		$realKey = md5($strKey);
		$needAdd = true;
		if($logContent){
			$num = 1;
			preg_match('/'.$realKey.':.*?\[(\d+)\]'.PHP_EOL.'/', $logContent, $result);
			if(isset($result[1])){
				$needAdd = false;
				$num += (int)$result[1];
				$newlogContent = preg_replace('/('.$realKey.':.*?)(\[\d+\])('.PHP_EOL.')/', '\1['.$num.']\3', $logContent);
				Basic::write($logPath, $newlogContent, false);
			}
		}
		if($needAdd){
			$realContent = $realKey . ':' . $strContent . '[1]';
			Basic::write($logPath, $realContent . PHP_EOL, false, true, false);
		}
	}
	/**
	 * 列出所有日志
	 * 
	 * @access public
	 */
	public static function listLogs(){
		return Basic::listDirTree(UNIFIED_LOG_PATH);
	}
	/**
	 * 列出指定日志的内容
	 * 
	 * @access public
	 * @param  path   $pathLogFile  日志路径
	 */
	public static function listLogContent($pathLogFile){
		return file($pathLogFile);
	}
}