<?php
/**
 * 请求管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreRequestCls
{
	private function __construct(){}//静态类，禁止构造对象
	
	/**
	 * 获取域名
	 * 
	 * @access public
	 * @param  bool  $withSuffix  是否包含域名前缀
	 * 否则获取一个不带域名前缀的值（用于设置cookie/session在所有域名与子域名中有效）
	 * @return domain
	 */
	public static function getDomain($withSuffix = true){
		$host = $_SERVER['HTTP_HOST'];
		$start = strpos($host, '.');
		$start = $withSuffix || $start === false ? 0 : $start;
		$end = strrpos($host, ':');
		$end = $end === false ? strlen($host) : $end;
		return substr($host, $start, $end - $start);
	}
	
	/**
	 * 获取客户端IP地址
	 * 
	 * @access public
	 * @param  integer  $type     返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param  boolean  $advFlag  是否进行高级模式获取（有可能被伪装）
	 * @return array
	 */
	public static function getIp($type = 0, $advFlag = false){
		$type = $type ? 1 : 0;
		static $ip = null;
		if(null !== $ip) return $ip[$type];
		if($advFlag){
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
		return $ip[$type];
	}
}
