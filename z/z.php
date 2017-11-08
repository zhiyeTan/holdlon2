<?php
/**
 * 框架引导机制
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class z
{
	/**
	 * 自动加载类名文件
	 * 结合命名空间使用
	 */
	public static function autoload($strClassName){
		$filePath = UNIFIED_PATH . strtr($strClassName, array('\\'=>Z_DS)) . '.php';
		if(!is_file($filePath)){
			return;
		}
		include($filePath);
		if(!class_exists($strClassName, false) && !interface_exists($strClassName, false)){
			die("Unable to find '$strClassName' in file: $filePath. Namespace missing?");
		}
	}
}
//使用自定义的类加载机制
spl_autoload_register(array('z', 'autoload'), true, true);
//运行应用
(new z\core\App())->run();