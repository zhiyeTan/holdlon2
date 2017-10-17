<?php
namespace z\core;

use z\lib\Basic;

/**
 * 应用管理
 *
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 *
 */
class App{
	public function run(){
		//初始化配置项
		Config::init();
		//设置调试模式，生产环境请设置关闭
		Config::setDebugModel(true);
		//加载预定义的常量
		Config::loadConstant();
		//解析请求
		Router::parse(Request::getQueryString());
		//设置应用路径
		if(!Config::setAppPath()){
			//报错：入口不存在
			(new Controller())->displayError(404, ERR_ENTRY_NOT_EXIST);
		}
		//加载应用配置
		Config::loadAppConfig();
		//根据配置设置时区
		date_default_timezone_set(Config::$options['default_timezone']);
		//获取控制器文件路径
		$controllerFilePath = Config::getControllerPath();
		if(!is_file($controllerFilePath)){
			//报错：控制器不存在
			(new Controller())->displayError(404, ERR_CONTROLLER_NOT_EXIST);
		}
		//初始化控制器对象
		$alias = Config::getControllerAlias();
		$object = new $alias();
		//这里需要应用静态缓存
		$cached = Locafis::get();
		if($cached){
			Response::init()->setCache(0)->setContent($cached);
		}
		else{
			if(!method_exists($object, 'main')){
				//报错：控制器主方法不存在
				(new Controller())->displayError(404, ERR_CONTROLLER_METHOD_NOT_EXIST);
			}
			// 分别执行GET参数、POST参数的安全校验以及主方法
			$object->keepSafeQuest();
			$object->keepSafeQuest(false);
			$object->main();
		}
		// 发送响应
		Response::init()->send();
		// 尝试执行可能存在的延后操作
		if(method_exists($object, 'delay')){
			$object->delay();
		}
	}

}
