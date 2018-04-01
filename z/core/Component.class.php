<?php
/**
 * 渲染器机制
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreComponentCls
{
	use zCoreCompileTra;
	
	/**
	 * 渲染模板
	 * @access public
	 * @param  string  $typeName  组件类型名
	 * @param  string  $subName   组件子类型名
	 * @param  string  $viewName  视图名
	 * @param  array   $data      要导入的数据
	 */
	public static function render($typeName, $subName, $viewName, $data){
		$compilePath = COMPONENT_COMPILE_PATH . $typeName . Z_DS . $subName . Z_DS;
		$compileFile = $compilePath . $viewName . '.php';
		if(!zCoreConfigCls::$options['compile_enable'] || !is_file($compileFile)){
			zPubBasicLib::mkFolder($compilePath);
			$viewFile = COMPONENT_PATH . $typeName . Z_DS . $subName . Z_DS . $viewName . TEMPLATE_SUFFIX;
			zPubBasicLib::write($compileFile, self::compile(zPubBasicLib::read($viewFile)));
		}
		//将数组键值对转换成多个变量值
		is_array($data) ? extract($data) : '';
		//打开缓冲区
		ob_start();
		//载入模板
		include $compileFile;
		//返回缓冲内容并清空
		return ob_get_clean();
	}
}
