<?php
/**
 * 控制器
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
trait zCoreControllerTra
{
	//受许可的GET参数的键名数组，如：['cid', 'keyword', 'page']
	//需验证的GET参数的键名及规则值对数组，如：['cid'=>'int', 'keyword'=>'addslashes', 'page'=>'int']
	//需过滤的GET参数的键名，默认值，规则数组，如：[['cid', 0, 'int'], ['keyword', '', 'addslashes', ['page', 1, 'int']]]
	//POST同理
	protected $getAllowedKeys = [];
	protected $getValidationRules = [];
	protected $getFilterRules = [];
	protected $postAllowedKeys = [];
	protected $postValidationRules = [];
	protected $postFilterRules = [];
	
	protected $data = [];//数据栈
	
	protected $errno = 0;
	protected $message = '';
	
	public $cache;//缓存
	
	/**
	 * 赋值到数据栈中
	 * @access public
	 * @param  string  $key    键名或键值对数组
	 * @param  string  $value  键值（$key为非数组时有效）
	 */
	public function assign($key, $value = ''){
		if(is_array($key)){
			foreach($key as $k => $v){
				$this->data[$k] = $v;
			}
		}
		else{
			$this->data[$key] = $value;
		}
	}

	/**
	 * 校验请求
	 * @access public
	 * @param  boolean  $checkGET  目标函数是否为GET
	 */
	public function keepSafeQuest($checkGET = true){
		//分别确定目标数组、许可键名数组、验证数组、过滤数组、基础键名数组、异常日志文件名
		$target  = $checkGET ? $_GET : $_POST;
		$allows  = $checkGET ? $this->getAllowedKeys : $this->postAllowedKeys;
		$verifys = $checkGET ? $this->getValidationRules : $this->postValidationRules;
		$filters = $checkGET ? $this->getFilterRules : $this->postFilterRules;
		$basics  = $checkGET ? ['e', 'c'] : ['token'];
		$logName = $checkGET ? 'illegalGetLog' : 'abnormalPoscho ';

		$error = false;
		//获得不被允许的参数键名(空表示不限制传入参数)
		$diff = $allows ? array_diff(array_keys($target), array_merge($basics, array_keys($allows))) : '';
		//验证参数的合法性
		foreach($verifys as $k => $rule){
			//存在且不合法时标记错误
			if(isset($target[$k]) && !zCoreSafeCls::verify($target[$k], $rule)){
				$error = true;
				break;
			}
		}
		//若存在差异键名或非法验证，记录请求信息到日志中
		if($diff || $error){
			$content = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ';
			$content .= zCoreRequestCls::getIp(0) . ' ';
			$content .= $checkGET ? $_SERVER['REQUEST_URI'] : var_export($_POST, true);
			zCoreLogCls::save($logName, $content);
			//参数不合法时触发错误处理
			if($error){
				trigger_error(T_ILLEGAL_PARAMETER, E_USER_ERROR);
			}
			//删除多余的参数
			foreach($diff as $v){
				if($checkGET){
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
				$tmpValue = zCoreSafeCls::filter($target[$row[0]], $row[2]);
				if($checkGET){
					$_GET[$row[0]] = $tmpValue ?? $row[1];
				}
				else{
					$_POST[$row[0]] = $tmpValue ?? $row[1];
				}
			}
		}
	}
}
