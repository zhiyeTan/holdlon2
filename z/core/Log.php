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
	 * 记录日志
	 * 
	 * @access public
	 * @param  string  $strFileName  日志文件名
	 * @param  string  $strContent   单条日志的内容
	 * @return boolean
	 */
	public static function save($strFileName, $strContent){
		$withoutSuffixPath = self::getLogsPath() . $strFileName;
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
	 * 分为记录型recordType和分析型analysisType
	 * 记录分析型日志
	 * 每行的格式为MD5($strKey):$strContent . PHP_EOL
	 * 其中$strContent必须包含一个可变值，其格式为[数字]
	 * 若存在MD5($strKey)时则更新“[数字]”中的数字，否则插入到新行中
	 * 
	 * @access public
	 * @param  string  $strFileName  日志文件名
	 * @param  string  $strKey       作为识别键用的唯一字符串，在日志中的MD5加密串
	 * @param  string  $strValue     变更值，允许带运算符号，如：+、-、*、/
	 * @param  string  $strContent   内容
	 */
	private static function saveAnalysisTypeLog($strFileName, $strContent, $strKey, $strValue){
		$logPath = self::getLogsPath() . $strFileName . '.txt';
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