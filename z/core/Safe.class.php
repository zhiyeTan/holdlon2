<?php
/**
 * 验证机制
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreSafeCls
{
	private function __construct(){}//禁止实例化
	
	/**
	 * 验证值是否为有效格式
	 * @access public
	 * @param  string   $rule   验证规则
	 * @param  mixed    $value  字段值
	 * @return boolean
	 */
	public static function verify($value, $rule){
		switch($rule){
			case 'require'://必须
				$result = !$value || '0' == $value;
				break;
			case 'accepted'://接受
				$result = in_array($value, ['1', 'on', 'yes']);
				break;
			case 'date'://是否是一个有效日期
				$result = false !== strtotime($value);
				break;
			case 'alpha'://只允许字母
				$result = self::regex($value, '/^[A-Za-z]+$/');
				break;
			case 'alphaNum'://只允许字母和数字
				$result = self::regex($value, '/^[A-Za-z0-9]+$/');
				break;
			case 'alphaDash'://只允许字母、数字和下划线 破折号
				$result = self::regex($value, '/^[A-Za-z0-9\-\_]+$/');
				break;
			case 'chs'://只允许汉字
				$result = self::regex($value, '/^[\x{4e00}-\x{9fa5}]+$/u');
				break;
			case 'chsAlpha'://只允许汉字、字母
				$result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u');
				break;
			case 'chsAlphaNum'://只允许汉字、字母和数字
				$result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u');
				break;
			case 'chsDash'://只允许汉字、字母、数字和下划线_及破折号-
				$result = self::regex($value, '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u');
				break;
			case 'ip'://是否为IP地址
				$result = self::checkByfilterVar($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
				break;
			case 'url'://是否为一个URL地址
				$result = self::checkByfilterVar($value, FILTER_VALIDATE_URL);
				break;
			case 'int'://是否为整型
				$result = is_int($value);
				break;
			case 'float'://是否为float
				$result = is_float($value);
				break;
			case 'number'://是否为数字
				$result = is_numeric($value);
				break;
			case 'email'://是否为邮箱地址
				$result = self::checkByfilterVar($value, FILTER_VALIDATE_EMAIL);
				break;
			case 'boolean'://是否为布尔值
				$result = in_array($value, [0, 1, true, false]);
				break;
			case 'token'://安全令牌验证
				$result = self::token($value);
				break;
			default://正则验证
				$result = self::regex($value, $rule);
		}
		return $result;
	}
	
	/**
	 * 过滤为合法的值（主要针对数据库类型进行过滤）
	 * @access public
	 * @param  string   $rule   验证规则
	 * @param  mixed    $value  字段值
	 * @return mixed
	 */
	public static function filter($value, $rule){
		$value = trim($value);
		switch($rule){
			case 'boolean':
				//布尔型
				$result = !!$value;
				break;
			case 'int':
				//整数
				$result = (int)$val;
				break;
			case 'float':
				//浮点数
				$result = (float)$val;
				break;
			case 'year':
				//对应mysql的time类型
				$result = self::timeFilter($value, 'year');
				break;
			case 'date':
				//对应mysql的date类型
				$result = self::timeFilter($value, 'date');
				break;
			case 'time':
				//对应mysql的time类型
				$result = self::timeFilter($value, 'time');
				break;
			case 'datetime':
				//对应mysql的datetime类型
				$result = self::timeFilter($value, 'datetime');
				break;
			case 'timestamp':
				//无格式时间戳
				$result = self::timeFilter($value);
				break;
			case 'nl2br':
				//换行符过滤为<br>
				$result = nl2br($value);
				break;
			case 'notag':
				//去掉标记
				$result = strip_tags($value);
				break;
			case 'html':
				//转为html格式
				$result = htmlspecialchars($value);
				break;
			case 'addslashes':
				//默认转义单双引号、反斜线及NULL
				$result = get_magic_quotes_gpc() ? $value : addslashes($value);
				break;
			default:
				//正则过滤
				$result = self::regexFilter($value, $rule);
		}
		return $result;
	}

	/**
	 * 使用正则验证
	 * @access protected
	 * @param  mixed      $value  值
	 * @param  mixed      $rule   验证规则 正则规则或者预定义正则名
	 * @return mixed
	 */
	protected static function regex($value, $rule){
		if(0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)){
			//不是正则表达式则两端补上/
			$rule = '/^' . $rule . '$/';
		}
		return 1 === preg_match($rule, (string) $value);
	}
	
	/**
	 * 使用filter_var方式验证
	 * @access protected
	 * @param  mixed      $value  值
	 * @param  mixed      $rule   验证规则
	 * @return boolean
	 */
	protected static function checkByfilterVar($value, $rule){
		if(is_string($rule) && strpos($rule, ',')){
			list($rule, $param) = explode(',', $rule);
		}
		elseif (is_array($rule)){
			$param = isset($rule[1]) ? $rule[1] : null;
		}
		else{
			$param = null;
		}
		return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
	}
	
	/**
	 * 验证表单令牌
	 * @access protected
	 * @param  mixed  $value  值
	 * @return boolean
	 */
	protected static function token($value){
		$token = zCoreSessionCls::get('__token__');
		if(!$token){
			//令牌无效
			return false;
		}
		if($token === $value){
			//验证完成即销毁，防止重复提交
			zCoreSessionCls::delete('__token__');
			return true;
		}
		//重置令牌
		zCoreSessionCls::delete('__token__');
		return false;
	}
	
	/**
	 * 时间过滤
	 * @access protected
	 * @param  string/number  $value  值
	 * @param  string         $type   时间类型
	 * @return string/number
	 */
	protected static function timeFilter($value, $type = ''){
		//如果时间戳不合法，重置为当前时间戳
		if(is_numeric($value)){
			$len = strlen((int)$value);
			if($len != 9 && $len != 10){
				$value = time();
			}
		}
		//如果时间格式不合法，重置为当前时间戳
		else{
			$value = strtotime($value);
			$value = $value ? $value : time();
		}
		if(!$type){
			return $value;
		}
		switch ($type){
			case 'year':
				$format = 'Y';
				break;
			case 'date':
				$format = 'Y-m-d';
				break;
			case 'time':
				$format = 'H:i:s';
				break;
			default:
				$format = 'Y-m-d H:i:s';
		}
		return date($format, $value);
	}
	
	/**
	 * 使用正则过滤
	 * @access protected
	 * @param  string  $value  值
	 * @param  string  $rule   验证规则 正则规则或者预定义正则名
	 * @return string
	 */
	protected static function regexFilter($value, $rule){
		if(0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)){
			//不是正则表达式则两端补上/
			$rule = '/^' . $rule . '$/';
		}
		return preg_replace($rule, '', $value);
	}
}
