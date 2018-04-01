<?php
/**
 * 渲染器机制
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
trait zCoreCompileTra
{
	//语法捕获表达式映射
	private static $regExpMap = [
		'var'		=> '/\{\\$.*?\}/',
		'foreach'	=> '/\{foreach.*?\}/i',
		'if'		=> '/\{if.*?\}/i',
		'elseif'	=> '/\{elseif.*?\}/i',
		'else'		=> '/\{else.*?\}/i',
		'end'		=> '/\{\/.*?\}/i'
	];
	//语法解析方法映射
	private static $actionMap = [
		'var'		=> 'replaceVar',
		'foreach'	=> 'replaceForeach',
		'if'		=> 'replaceIf',
		'elseif'	=> 'replaceElseIf',
		'else'		=> 'replaceElse',
		'end'		=> 'replaceEnd'
	];
	
	/**
	 * 编译模板内容
	 * @access private
	 * @param  sting  $content  模板内容
	 * @return string
	 */
	private static function compile($content){
		$content = self::parseLanguage('var', $content);
		$content = self::parseLanguage('foreach', $content);
		$content = self::parseLanguage('if', $content);
		$content = self::parseLanguage('elseif', $content);
		$content = self::parseLanguage('else', $content);
		$content = self::parseLanguage('end', $content);
		return $content;
	}

	/**
	 * 解析模板语言
	 * @access private
	 * @param  sting  $type     语言类型
	 * @param  sting  $content  模板内容
	 * @return string
	 */
	private static function parseLanguage($type, $content){
		preg_match_all(self::$regExpMap[$type], $content, $res);
		$pattern = [];
		if(isset($res[0])){
			foreach($res[0] as $k => $v){
				//组成替换数组
				$tmpMethod = self::$actionMap[$type];
				$pattern[$v] = self::$tmpMethod($v);
			}
		}
		return strtr($content, $pattern);
	}

	/**
	 * 替换变量
	 * @access private
	 * @param  sting  $content  内容
	 * @return string
	 */
	private static function replaceVar($content){
		//接着替换其中的变量
		$content = self::publicReplaceVar($content);
		//替换为php代码
		$content = preg_replace('/\{(.*?)\}/', '<?php echo \\1; ?>', $content);
		return $content;
	}

	/**
	 * 替换foeach语句
	 * @access private
	 * @param  sting  $content  内容
	 * @return string
	 */
	private static function replaceForeach($content){
		preg_match('/from=(\\$\S*)/i', $content, $from);
		preg_match('/key=([a-zA-Z_][a-zA-Z0-9_]*)/i', $content, $key);
		preg_match('/item=([a-zA-Z_][a-zA-Z0-9_]*)/i', $content, $item);
		//修正获得的字符串
		$from = self::publicReplaceVar($from[1]);
		$key = trim(trim($key[1], '"'), "'");
		$item = trim(trim($item[1], '"'), "'");
		//组成替换数组
		return '<?php foreach(' . $from . ' as $' . $key . '=>$' . $item . ') { ?>';
	}

	/**
	 * 替换if语句
	 * @access private
	 * @param  sting  $content  内容
	 * @return string
	 */
	private static function replaceIf($content){
		//替换为php代码
		$content = preg_replace('/\{if(.*?)\}/i', '<?php if(\\1) { ?>', $content);
		return self::publicReplaceVar($content);
	}

	/**
	 * 替换elseif语句
	 * @access private
	 * @param  sting  $content  内容
	 * @return string
	 */
	private static function replaceElseIf($content){
		//替换为php代码
		$content = preg_replace('/\{elseif(.*?)\}/i', '<?php } elseif(\\1) { ?>', $content);
		return self::publicReplaceVar($content);
	}

	/**
	 * 替换elseif语句
	 * @access private
	 * @param  sting  $content  内容
	 * @return string
	 */
	private static function replaceElse($content){
		return preg_replace('/\{else(.*?)\}/i', '<?php } else { ?>', $content);
	}

	/**
	 * 替换结束语句
	 * @access private
	 * @param  sting  $content  内容
	 * @return string
	 */
	private static function replaceEnd($content){
		//替换为php代码
		return preg_replace('/\{\/.*?\}/i', '<?php } ?>', $content);
	}

	/**
	 * 替换变量
	 * @access private
	 * @param  sting  $content  内容
	 * @return string
	 */
	private static function publicReplaceVar($content){
		//{$value.key}
		$content = preg_replace('/\\$([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)/', "\$\\1['\\2']", $content);
		//{$value.number}
		$content = preg_replace('/\\$([a-zA-Z_][a-zA-Z0-9_]*)\.(\d*)/', "\$\\1[\\2]", $content);
		return $content;
	}
}
