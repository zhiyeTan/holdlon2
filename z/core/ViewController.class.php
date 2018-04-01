<?php
/**
 * 控制器
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreViewControllerCls
{
	use zCoreControllerTra;
	
	private static $viewName;
	
	protected $errViewName;//错误视图模版
	
	/**
	 * 构建函数
	 * @access public
	 */
	public function __construct(){
		self::$viewName = strtr(get_class($this), ['appCon'=>'','Cls'=>'']);
		$this->cache = zModCacheCls::getAppViewCache(self::$viewName);
	}
	
	/**
	 * 显示视图
	 * @access public
	 */
	public function display(){
		$content = $this->cache;
		if(!$content){
			$content = zCoreTemplateCls::render($this->data, $this->errno ? $this->errViewName : '');
			//修正静态资源的路径（不包括站外资源引用）
			$content = zCoreRouterCls::redirectStaticResources($content);
			if(!$this->errno){
				zModCacheCls::saveAppViewCache(self::$viewName, $content);
			}
		}
		zCoreResponseCls::init()->setContent($content)->send();
	}
}
