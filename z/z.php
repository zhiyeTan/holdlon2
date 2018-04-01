<?php

//定义统一的分隔符
const Z_DS = DIRECTORY_SEPARATOR;

//定义统一的框架路径
define('UNIFIED_PATH', dirname(dirname(__FILE__)) . Z_DS);

//定义统一的目录名映射(键名必须小写)
const UNIFIED_DIR_MAP = [
	'r'		=> 'read',
	'w'		=> 'write',
	'con'	=> 'controller',
	'mod'	=> 'model',
	'com'	=> 'common',
	'pub'	=> 'public',
	'ext'	=> 'extend'
];

//定义统一的后缀名映射(键名必须小写)
const UNIFIED_SUFFIX_MAP = [
	'cls'	=> 'class',
	'abs'	=> 'abstract',
	'tra'	=> 'trait',
	'lib'	=> 'library',
];

/**
 * 框架引导机制
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class z
{
	//自动加载类名文件
	public static function autoload($className){
		//类名分词
		$words = explode(',', strtolower(preg_replace('/([A-Z])/', ',\1', $className)));
		//词组长度小于4不符合解析规范
		//应用or模块的类统一由app类的自动加载方法去处理
		if(count($words) < 4 || $words[0] == 'app'){
			return false;
		}
		$suffix = end($words);
		$startLen = strlen($words[0].$words[1]);
		$realFileName = substr($className, $startLen, strlen($className) - $startLen - strlen($suffix));
		//构造真实路径
		$filePath = UNIFIED_PATH;
		if($words[0] == 'r' || $words[0] == 'w'){//模型类做一下特殊处理
			$filePath .= 'model' . Z_DS;
		}
		$filePath .= (UNIFIED_DIR_MAP[$words[0]] ?? $words[0]) . Z_DS;
		$filePath .= (UNIFIED_DIR_MAP[$words[1]] ?? $words[1]) . Z_DS;
		$filePath .= $realFileName . '.';
		$filePath .= UNIFIED_SUFFIX_MAP[$suffix] . '.php';
		include $filePath;
	}
}
//使用自定义的类加载机制
spl_autoload_register(['z', 'autoload'], true, true);
//运行应用
(new zCoreAppCls())->run();