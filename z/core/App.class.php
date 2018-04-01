<?php
/**
 * 应用管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreAppCls
{
	public function run(){
		//初始化
		zCoreConfigCls::init();
		//注册异常和错误处理方法
		zCoreExceptionCls::register();
		//解析请求
		zCoreRouterCls::parse();
		//加载应用配置
		zCoreConfigCls::configure();
		//添加应用or模块类的加载机制
		spl_autoload_register([__CLASS__, 'autoload'], true, true);
		//判断是否允许访问当前应用or模块
		if(!empty(APP_ALLOW_DIR) && APP_DIR != APP_DEFAULT_DIR && !in_array(APP_DIR, APP_ALLOW_DIR)){
			trigger_error(T_NO_PERMISSION_MODULE, E_USER_ERROR);
		}
		//初始化控制器类(必然在app/controller目录下，且必须为普通类)
		$className = 'appCon' . ucfirst(strtolower($_GET['c'])) . 'Cls';
		$object = new $className();
		//如果没有缓存则执行以下方法
		if(!$object->cache){
			//继续过来的控制器都必将具备以下方法(暂不考虑不通过继承创建的控制类)
			$object->keepSafeQuest();
			$object->keepSafeQuest(false);
			$object->main();
		}
		//显示结果并尝试执行可能存在的延后操作
		$object->display();
		if(method_exists($object, 'delay')){
			$object->delay();
		}
	}

	/**
	 * 应用或模块类的加载机制
	 * @access private
	 * @param  $className  类名
	 */
	private static function autoload($className){
		//类名分词
		$words = explode(',', strtolower(preg_replace('/([A-Z])/', ',\1', $className)));
		if(count($words) < 4){
			trigger_error(T_ILLEGAL_CLASS_FORMAT . $className, E_USER_ERROR);
		}
		if($words[0] != 'app'){
			return false;
		}
		$suffix = end($words);
		$startLen = strlen($words[0].$words[1]);
		$realFileName = substr($className, $startLen, strlen($className) - $startLen - strlen($suffix));
		//构造真实路径
		$filePath = $words[1] == 'com' ? UNIFIED_PATH . 'app' . Z_DS : APP_PATH;
		$filePath .= UNIFIED_DIR_MAP[$words[1]] . Z_DS;
		$filePath .= $realFileName . '.';
		$filePath .= UNIFIED_SUFFIX_MAP[$suffix] . '.php';
		if(!is_file($filePath)){
			trigger_error(T_CONTROLLER_NOT_EXIST, E_USER_ERROR);
		}
		include $filePath;
	}
}
