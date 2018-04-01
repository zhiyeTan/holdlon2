<?php
/**
 * 模板机制
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class zCoreTemplateCls
{
	use zCoreCompileTra;
	
	/**
	 * 解析组件语法
	 * 组件引用格式:{component:组件分类目录名/子分类目录名/视图名|接口控制器类名}
	 * eg: {component:typename/subname/tplname|appConDataApiCls}
	 * 数据模型类名允许省略，相当于不加载数据
	 * @access private
	 * @param  sting  $content  内容
	 * @return string
	 */
	private static function parseComponent($content){
		$pattern = [];
		preg_match_all('/\{component:(.*?)\}/', $content, $res);
		foreach($res[1] as $k => $v){
			$componentInfo = explode('|', $v);
			$pathInfo = explode('/', $componentInfo[0]);
			//读取组件的接口数据(接口接受的是当前请求的参数，多个组件时需要注意参数重叠的情况)
			//需要由URL参数控制不同输出的话，建议不采用组件策略，其逻辑将更为清晰
			if(!empty($componentInfo[1])){
				$object = new $componentInfo[1]();
				$object->main();
				$data = $object->data;
				unset($object);
			}
			$pattern[$res[0][$k]] = zCoreComponentCls::render($pathInfo[0], $pathInfo[1], $pathInfo[2], $data);
		}
		return strtr($content, $pattern);
	}
	
	/**
	 * 渲染视图模板
	 * @access public
	 * @param  array   $data      要导入的数据
	 * @param  string  $viewName  公共视图名
	 * @return string
	 */
	public static function render($data, $viewName = ''){
		$filePath = $viewName ? PUBLIC_VIEW_PATH . $viewName . TEMPLATE_SUFFIX : VIEW_FILE_PATH;
		if(!zCoreConfigCls::$options['compile_enable'] || !is_file(VIEW_COMPILED_FILE_PATH)){
			zPubBasicLib::mkFolder(APP_COMPILE_PATH);
			$content = self::parseComponent(zPubBasicLib::read($filePath));
			zPubBasicLib::write(VIEW_COMPILED_FILE_PATH, self::compile($content));
		}
		//将数组键值对转换成多个变量值
		is_array($data) ? extract($data) : '';
		//打开缓冲区
		ob_start();
		//载入模板
		include VIEW_COMPILED_FILE_PATH;
		//返回缓冲内容并清空
		return ob_get_clean();
	}
}
