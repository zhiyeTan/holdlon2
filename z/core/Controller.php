<?php

namespace z\core;

/**
 * 控制器
 *
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 *
 */
class Controller extends Template
{
	//受许可的GET参数的键名数组，如：array('cid', 'keyword', 'page')
	//需验证的GET参数的键名及规则值对数组，如：array('cid'=>'int', 'keyword'=>'addslashes', 'page'=>'int')
	//需过滤的GET参数的键名，默认值，规则数组，如：array(array('cid', 0, 'int'), array('keyword', '', 'addslashes', array('page', 1, 'int')))
	//POST同理
	protected static $allowGetKeys = array();
	protected static $verifyGetValues = array();
	protected static $filterGetValues = array();
	protected static $allowPostKeys = array();
	protected static $verifyPostValues = array();
	protected static $filterPostValues = array();

	/**
	 * 校验请求
	 * 
	 * @access public
	 * @param  boolean  $boolIsGET  目标函数是否为GET
	 */
	public function keepSafeQuest($boolIsGET = true){
		//分别确定目标数组、许可键名数组、验证数组、过滤数组、基础键名数组、异常日志文件名
		$target = $boolIsGET ? $_GET : $_POST;
		$allows = $boolIsGET ? self::$allowGetKeys : self::$allowPostKeys;
		$verifys = $boolIsGET ? self::$verifyGetValues : self::$verifyPostValues;
		$filters = $boolIsGET ? self::$filterGetValues : self::$filterPostValues;
		$basics = $boolIsGET ? array('e', 'm', 'c') : array('token');
		$logName = $boolIsGET ? 'illegalGetLog' : 'abnormalPostLog';

		$error = false;
		//获得不被允许的参数键名
		$diff = array_diff(array_keys($target), array_merge($basics, array_keys($allows)));
		//验证参数的合法性
		foreach($verifys as $k => $rule){
			//存在且不合法时标记错误
			if(isset($target[$k]) && !Safe::verify($target[$k], $rule)){
				$error = true;
				break;
			}
		}
		//若存在差异键名或非法验证，记录请求信息到日志中
		if($diff || $error){
			$content = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ';
			$content .= Request::getIp(0) . ' ';
			$content .= $boolIsGET ? $_SERVER['REQUEST_URI'] : var_export($_POST, true);
			Log::save($logName, $content);
			//参数不合法时直接输出错误
			if($error){
				$this->displayError(405, ERR_ILLEGAL_PARAMETER);
			}
			//删除多余的参数
			foreach($diff as $k){
				if($boolIsGET){
					unset($_GET[$v]);
				}
				else{
					unset($_POST[$v]);
				}
			}
		}
		//过滤参数
		foreach($filters as $row){
			if(isset($target[$row[0]])){
				$tmpValue = Safe::filter($target[$row[0]], $row[2]);
				$tmpValue = $tmpValue ?? $row[1];
				if($boolIsGET){
					$_GET[$row[0]] = $tmpValue;
				}
				else{
					$_POST[$row[0]] = $tmpValue;
				}
			}
		}
	}

	/**
	 * 读取API提供的数据
	 * 
	 * @access public
	 * @param  string    $strDirName            应用目录名
	 * @param  string    $strModule             模块名称
	 * @param  string    $strController         控制器名称
	 * @param  array     $arrArgs               请求参数
	 * @param  boolean   $boolCallDelayAction   是否调用api的延后函数
	 * @return array
	 */
	public function getApiData($strDirName, $strModule, $strController, $arrArgs = array(), $boolCallDelayAction = true){
		$apiData = array();
		//设置api对应的缓存名，并尝试获取缓存
		$apiBasicUrlParam = array(
			'm' => $strModule,
			'e' => $strDirName,
			'c' => $strController
		);
		$apiUrlParam = array_merge($apiBasicUrlParam, $arrArgs);
		$apiCacheFileName = Config::getCacheFileName(CACHE_TYPE_DATA, $apiUrlParam);
		//获取json数据
		$apiData = Locafis::get(CACHE_TYPE_DATA, $apiCacheFileName);
		if(!$apiData){
			//没有缓存则执行api接口函数
			$apiPath = Config::getControllerPath($strController, $strModule, $strDirName);
			$alias = Config::getControllerAlias($strController, $strModule);
			include $apiPath;
			$object = new $alias();
			if(method_exists($object, 'main')){
				$tmpGet = $_GET;
				//存放当前的GET参数
				$_GET = $arrArgs;
				//将请求参数放进GET中，以应用参数
				$object->main();
				$apiData = Response::getContent();
				//重置响应内容和类型
				Response::setContentType('html')->setContent('');
				//保存数据缓存
				Locafis::save($apiData, $apiCacheFileName);
				//重置为当前GET参数
				$_GET = $tmpGet;
			}
		}
		//是否调用api的延后函数
		if($boolCallDelayAction && method_exists($object, 'delay')){
			$object->delay();
		}
		return $apiData['data'];
	}
	
	/**
	 * 渲染一个友好的错误提示页
	 * 
	 * @access public
	 * @param  number  $intCode  状态码
	 * @param  string  $content  内容
	 */
	public function displayError($intCode, $strContent)
	{
		$content = '<div style="padding: 24px 48px;"><h1>&gt;_&lt;#</h1><p>' . $strContent . '</p>';
		Response::init()
			->setExpire(0)
			->setCache(0)
			->setCode($intCode)
			->setContent($content)
			->send();
		exit(0);
	}

	/**
	 * 渲染指定页面
	 *
	 * @access public
	 * @param  string  $strTemplateName  模板名
	 */
	public function display($strTemplateName){
		$this->render($strTemplateName);
	}

}
