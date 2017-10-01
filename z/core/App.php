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
		echo $_SERVER["HTTP_X_FORWARDED_PROTO"];
		
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
			//(new Controller())->displayError(404, ERR_ENTRY_NOEXIST);
		}
		//加载应用配置
		Config::loadAppConfig();
		//根据配置设置时区
		date_default_timezone_set(Config::$options['default_timezone']);
		
		//Log::save('2', 'htt://www.tzy.com/', '测试写入内容');
		
	}

}
