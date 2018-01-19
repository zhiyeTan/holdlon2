<?php
namespace z\core;

/**
 * 请求管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Request
{
	private function __construct(){}//静态类，禁止构造对象
	/**
	 * 获取查询字符串
	 * 
	 * @access public
	 * @return string
	 */
	public static function getQueryString(){
		return $_GET['s'] ?? '';
	}
	
	/**
	 * 获取域名
	 * 
	 * @access public
	 * @param  bool    $boolCurrent  是否取得当前域名
	 * 否则获取一个不带域名前缀的值（用于设置cookie/session在所有域名与子域名中有效）
	 * @return domain
	 */
	public static function getDomain($boolCurrent = true){
		$host = $_SERVER['HTTP_HOST'];
		$start = strpos($host, '.');
		$start = $boolCurrent || $start === false ? 0 : $start;
		$end = strrpos($host, ':');
		$end = $end === false ? strlen($host) : $end;
		return substr($host, $start, $end - $start);
	}
	
	/**
	 * 获取客户端IP地址
	 * 
	 * @access public
	 * @param  integer  $intType  返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param  boolean  $boolAdv  是否进行高级模式获取（有可能被伪装）
	 * @return array
	 */
	public static function getIp($intType = 0, $boolAdv = false){
		$intType = $intType ? 1 : 0;
		static $ip = null;
		if(null !== $ip) return $ip[$intType];
		if($boolAdv){
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$pos = array_search('unknown', $arr);
				if(false !== $pos) unset($arr[$pos]);
				$ip = trim(current($arr));
			}
			elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			elseif(isset($_SERVER['REMOTE_ADDR'])){
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		}
		elseif(isset($_SERVER['REMOTE_ADDR'])){
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
		//IP地址合法验证
		$long = sprintf("%u", ip2long($ip));
		$ip = $long ? [$ip, $long] : ['0.0.0.0', 0];
		return $ip[$intType];
	}
}
