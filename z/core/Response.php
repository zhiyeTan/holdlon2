<?php

namespace z\core;

use z;

/**
 * 响应管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Response
{
	//状态码地图（常用200、301、304、401、404）
	private static $codeMap = [
		100 => 'HTTP/1.1 100 Continue',
		101 => 'HTTP/1.1 101 Switching Protocols',
		200 => 'HTTP/1.1 200 OK',
		201 => 'HTTP/1.1 201 Created',
		202 => 'HTTP/1.1 202 Accepted',
		203 => 'HTTP/1.1 203 Non-Authoritative Information',
		204 => 'HTTP/1.1 204 No Content',
		205 => 'HTTP/1.1 205 Reset Content',
		206 => 'HTTP/1.1 206 Partial Content',
		300 => 'HTTP/1.1 300 Multiple Choices',
		301 => 'HTTP/1.1 301 Moved Permanently',
		302 => 'HTTP/1.1 302 Found',
		303 => 'HTTP/1.1 303 See Other',
		304 => 'HTTP/1.1 304 Not Modified',
		305 => 'HTTP/1.1 305 Use Proxy',
		307 => 'HTTP/1.1 307 Temporary Redirect',
		400 => 'HTTP/1.1 400 Bad Request',
		401 => 'HTTP/1.1 401 Unauthorized',
		402 => 'HTTP/1.1 402 Payment Required',
		403 => 'HTTP/1.1 403 Forbidden',
		404 => 'HTTP/1.1 404 Not Found',
		405 => 'HTTP/1.1 405 Method Not Allowed',
		406 => 'HTTP/1.1 406 Not Acceptable',
		407 => 'HTTP/1.1 407 Proxy Authentication Required',
		408 => 'HTTP/1.1 408 Request Time-out',
		409 => 'HTTP/1.1 409 Conflict',
		410 => 'HTTP/1.1 410 Gone',
		411 => 'HTTP/1.1 411 Length Required',
		412 => 'HTTP/1.1 412 Precondition Failed',
		413 => 'HTTP/1.1 413 Request Entity Too Large',
		414 => 'HTTP/1.1 414 Request-URI Too Large',
		415 => 'HTTP/1.1 415 Unsupported Media Type',
		416 => 'HTTP/1.1 416 Requested range not satisfiable',
		417 => 'HTTP/1.1 417 Expectation Failed',
		500 => 'HTTP/1.1 500 Internal Server Error',
		501 => 'HTTP/1.1 501 Not Implemented',
		502 => 'HTTP/1.1 502 Bad Gateway',
		503 => 'HTTP/1.1 503 Service Unavailable',
		504 => 'HTTP/1.1 504 Gateway Time-out' 
	];
	//内容类型地图
	private static $contentTypeMap = [
		'html'			=> 'Content-Type: text/html; charset=utf-8',
		'plain'			=> 'Content-Type: text/plain',
		'jpeg'			=> 'Content-Type: image/jpeg',
		'zip'			=> 'Content-Type: application/zip',
		'pdf'			=> 'Content-Type: application/pdf',
		'mpeg'			=> 'Content-Type: audio/mpeg',
		'css'			=> 'Content-type: text/css',
		'javascript'	=> 'Content-type: text/javascript',
		'json'			=> 'Content-type: application/json',
		'xml'			=> 'Content-type: text/xml',
		'flash'			=> 'Content-Type: application/x-shockw**e-flash'
	];
	private static $api_errno = E_NONE;//api请求的错误标记
	private static $api_message = '';//api请求的提示信息
	private static $code = 200;//状态码
	private static $contentType = 'html';//内容类型
	private static $content;//响应内容
	private static $expire;//本地缓存时间
	private static $cache;//是否使用静态缓存
	private static $_instance;//类实例
	private function __construct(){}//禁止直接创建对象
	//禁止用户复制对象实例
	public function __clone(){
		trigger_error('Clone is not allow' ,E_USER_ERROR);
	}
	
	/**
	 * 单例构造方法
	 * 这个单例只是为了链式操作
	 * 
	 * @access public
	 * @return class
	 */
	public static function init(){
		if(!self::$_instance){
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	/**
	 * 获取响应内容
	 * 此函数主要用来获得接口生成的数据
	 * 
	 * @access public
	 * @param  mixed   $content  响应内容
	 * @return mixed
	 */
	public static function getContent(){
		return self::$content;
	}
	
	/**
	 * 设置内容类型
	 * 
	 * @access public
	 * @param  string  $type  内容类型
	 * @return class
	 */
	public static function setContentType($type){
		if(in_array($type, array_keys(self::$contentTypeMap))){
			self::$contentType = $type;
		}
		return self::$_instance;
	}
	
	/**
	 * 设置响应内容
	 * 当响应内容为数组时，自动修正响应类型为json
	 * 
	 * @access public
	 * @param  mixed   $content  响应内容
	 * @return class
	 */
	public static function setContent($content){
		//如果响应内容为数组，修正内容类型为json
		if(is_array($content)){
			self::$contentType = 'json';
		}
		//格式化JSON
		if(self::$contentType == 'json'){
			$content = [
				'errno'		=> self::$api_errno,
				'message'	=> self::$api_message,
				'data'		=> $content
			];
			$content = json_encode($content);
		}
		//修正静态资源的路径（不包括站外资源引用）
		//如果json的值为html并包含静态资源的话，必须在外部转为HTML实体时进行修正
		$content = Router::redirectStaticResources($content);
		//保存到属性中
		self::$content = $content;
		return self::$_instance;
	}
	
	/**
	 * 设置响应状态码
	 * 
	 * @access public
	 * @param  number  $code  状态吗
	 * @return class
	 */
	public static function setCode($code){
		if(in_array($code, array_keys(self::$codeMap))){
			self::$code = $code;
		}
		return self::$_instance;
	}
	
	/**
	 * 设置本地缓存时间
	 * 
	 * @access public
	 * @param  number  $timeStamp  有效时间（单位s）
	 * @return class
	 */
	public static function setExpire($timeStamp){
		self::$expire = (int) $timeStamp;
		return self::$_instance;
	}
	
	/**
	 * 设置是否使用静态缓存
	 * 
	 * @access public
	 * @param  boolean  $state  是否使用缓存
	 * @return class
	 */
	public static function setCache($state){
		self::$cache = !!$state;
		return self::$_instance;
	}
	
	/**
	 * 设置API错误标记
	 * 
	 * @access public
	 * @param  int  $intErrno  错误号
	 * @return class
	 */
	public static function setApiErrno($intErrno){
		self::$api_errno = $intErrno;
		return self::$_instance;
	}
	
	/**
	 * 设置API错误信息
	 * 
	 * @access public
	 * @param  string  $msg  错误信息
	 * @return class
	 */
	public static function setApiMessage($msg){
		self::$api_message = $msg;
		return self::$_instance;
	}
	
	/**
	 * 发送数据到客户端
	 * 
	 * @access public
	 */
	public static function send(){
		//检查 HTTP 表头是否已被发送
		if(!headers_sent()){
			$expire = self::$expire ?? Config::$options['local_expire'];
			//发送头部信息
			header(self::$codeMap[self::$code]);
			header('Content-language: ' . Config::$options['default_lang']);
			header('Cache-Control: max-age=' . $expire . ',must-revalidate');
			header('Last-Modified:' . gmdate('D,d M Y H:i:s') . ' GMT');
			header('Expires:' . gmdate('D,d M Y H:i:s', $_SERVER['REQUEST_TIME'] + $expire) . ' GMT');
			header(self::$contentTypeMap[self::$contentType]);
		}
		//未设置时，采用配置中对应的状态设置
		if(!self::$cache && self::$cache !== false){
			$cacheType = self::$contentType == 'json' ? CACHE_TYPE_DATA : CACHE_TYPE_STATIC;
			self::$cache = Config::whetherUseStaticCache($cacheType);
		}
		//成功且有相应内容并使用缓存时保存缓存
		if(200 == self::$code && self::$content && self::$cache){
			Locafis::save(self::$content);
		}
		echo self::$content;
		if(function_exists('fastcgi_finish_request')){
			//提高页面响应
			fastcgi_finish_request();
		}
	}
}
