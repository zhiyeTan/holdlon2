<?php
/**
 * 控制器
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreDataControllerCls
{
	use zCoreControllerTra;
	
	private static $viewName;
	
	/**
	 * 构建函数
	 * @access public
	 */
	public function __construct(){
		self::$viewName = strtr(get_class($this), ['appCon'=>'','Cls'=>'']);
		$this->cache = zModCacheCls::getAppDataCache(self::$viewName);
	}
	
	/**
	 * 显示视图
	 * @access public
	 */
	public function display(){
		if($this->cache){
			$json = json_encode($this->cache);
		}
		else{
			//保证errno、message在最前，且不被data数据覆盖
			$jsonData = ['errno'=>0, 'message'=>''];
			$jsonData = array_merge($jsonData, $this->data, [
				'errno'		=> $this->errno,
				'message'	=> $this->message
			]);
			//修正静态资源的路径（不包括站外资源引用）
			//如果json的值为html并包含静态资源的话，必须在外部转为HTML实体时进行修正
			$json = zCoreRouterCls::redirectStaticResources(json_encode($jsonData));
			if(!$this->errno){
				zModCacheCls::saveAppDataCache(self::$viewName, $json);
			}
		}
		zCoreResponseCls::init()
		->setContentType('json')
		->setContent($json)
		->send();
	}
}
