<?php
/**
 * 日志管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreLogCls
{
	private static $logsPath;//日志路径
	private function __construct(){}//静态类，禁止实例化
	
	/**
	 * 保存一条日志
	 * @access public
	 * @param  string  $fileName             日志文件名
	 * @param  string  $magicVal             当使用第三个参数时，作为分析型日志的key，否则作为记录型日志的内容
	 * @param  string  $analysisTypeContent  分析型日志要保存的内容
	 * @return boolean
	 */
	public static function save($fileName, $magicVal, $analysisTypeContent = ''){
		zPubBasicLib::mkFolder(APP_LOG_PATH);
		if($analysisTypeContent){
			self::saveAnalysisTypeLog($fileName, $magicVal, $analysisTypeContent);
		}
		else{
			self::saveRecordTypeLog($fileName, $magicVal);
		}
	}
	
	/**
	 * 保存记录型的日志
	 * @access private
	 * @param  string  $fileName  日志文件名
	 * @param  string  $content   单条日志的内容
	 * @return boolean
	 */
	private static function saveRecordTypeLog($fileName, $content){
		$withoutSuffixPath = APP_LOG_PATH . $fileName;
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
				if(!($state = rename($logPath, $newPath))){
					usleep(100);//延迟100毫秒
				}
			}
			while(!$state && $i < $domax);
		}
		zPubBasicLib::write($logPath, $content . PHP_EOL, true, false);
	}
	
	/**
	 * 保存分析型的日志
	 * 保存新日志时自动在该行后面加上[1]
	 * 保存一条已存在的记录时，则自动对对应行后面的数字累加并保存
	 * @access private
	 * @param  string  $fileName  日志文件名
	 * @param  string  $key       作为识别键用的唯一字符串
	 * @param  string  $content   内容
	 */
	private static function saveAnalysisTypeLog($fileName, $key, $content){
		$logPath = APP_LOG_PATH . $fileName . '.txt';
		$logContent = zPubBasicLib::read($logPath);
		$realKey = md5($key);
		$needAdd = true;
		if($logContent){
			$num = 1;
			preg_match('/'.$realKey.':.*?\[(\d+)\]'.PHP_EOL.'/', $logContent, $result);
			if(isset($result[1])){
				$needAdd = false;
				$num += (int)$result[1];
				$newlogContent = preg_replace('/('.$realKey.':.*?)(\[\d+\])('.PHP_EOL.')/', '\1['.$num.']\3', $logContent);
				zPubBasicLib::write($logPath, $newlogContent);
			}
		}
		if($needAdd){
			$realContent = $realKey . ':' . $content . '[1]';
			zPubBasicLib::write($logPath, $realContent . PHP_EOL, true, false);
		}
	}
	
	/**
	 * 列出所有日志
	 * @access public
	 */
	public static function listLogs(){
		return zPubBasicLib::listDirTree(UNIFIED_LOG_PATH);
	}
	
	/**
	 * 列出指定日志的内容
	 * @access public
	 * @param  path   $pathLogFile  日志路径
	 */
	public static function listLogContent($pathLogFile){
		return file($pathLogFile);
	}
}