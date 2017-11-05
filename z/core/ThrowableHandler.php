<?php
namespace z\core;

class ThrowableHandler extends \Exception
{
	private static $_code;
	private static $_message;
	private static $_file;
	private static $_line;
	private static $fatalType = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];
	//重定义构造器使 message 变为必须被指定的属性  
	public function __construct($message, $code = 0){
		//确保所有变量都被正确赋值
		parent::__construct($message, $code);
		self::setAttribute($this->code, $this->message, $this->file, $this->line);
	}
	
	/**
	 * 注册异常处理
	 * 
	 * @access public
	 */
	public static function register(){
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
	}
	
	/**
	 * 错误处理方法
	 * 
	 * @access public
     * @param  int     $intCode   错误编号
     * @param  string  $strMsg    详细错误信息
     * @param  path    $pathFile  出错的文件
     * @param  int     $intLine   出错行号
	 */
	public static function appError($intCode, $strMsg, $pathFile, $intLine){
		if(error_reporting() && $intCode){
			self::setAttribute($intCode, $strMsg, $pathFile, $intLine);
			self::tips();
		}
	}
	
	/**
	 * 异常处理方法
	 * 
	 * @access public
	 * @param  object  $objE  异常类
	 */
	public static function appException($objE){
		self::setAttribute($objE->getCode(), $objE->getMessage(), $objE->getFile(), $objE->getLine());
		self::tips();
	}
	
	/**
	 * 终止时的处理方法
	 * 
	 * @access public
	 */
	public static function appShutdown(){
		if(!is_null($error = error_get_last()) && in_array($error['type'], self::$fatalType)){
			self::setAttribute($error['type'], $error['message'], $error['file'], $error['line']);
			self::tips();
		}
	}
	
	/**
	 * 设置类属性
	 * 
	 * @access public
     * @param  int     $intCode   错误编号
     * @param  string  $strMsg    详细错误信息
     * @param  path    $pathFile  出错的文件
     * @param  int     $intLine   出错行号
	 */
	public static function setAttribute($intCode, $strMsg, $pathFile, $intLine){
		self::$_code = $intCode;
		self::$_message = $strMsg;
		self::$_file = $pathFile;
		self::$_line = $intLine;
	}
	
	/**
	 * 使用友好的方式输出提示
	 * 
	 * @access public
	 * @return string
	 */
	public static function getFriendlyTips(){
		return '<div style="padding: 24px 48px;"><h1>&gt;_&lt;#</h1><p>' . self::$_message . '</p>';
	}
	
	/**
	 * 使用规范的方式输出提示
	 * 
	 * @access public
	 * @return string
	 */
	public static function getNormTips(){
		$content  = '<div style="padding: 24px 48px;"><h1>&gt;_&lt;#</h1>';
		$content .= '<p>code: ' . self::$_code . '</p>';
		$content .= '<p>message: ' . self::$_message . '</p>';
		$content .= '<p>file: ' . self::$_file . '</p>';
		$content .= '<p>line: ' . self::$_line . '</p>';
		return $content;
	}
	
	/**
	 * 输出提示
	 * 
	 * @access public
	 */
	public static function tips(){
		$content = Config::$options['tips_mode'] ? self::getNormTips() : self::getFriendlyTips();
		Response::init()
			->setExpire(0)
			->setCache(0)
			->setCode(404)
			->setContent($content)
			->send();
		exit(0);
	}
}