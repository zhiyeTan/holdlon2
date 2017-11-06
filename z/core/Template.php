<?php

namespace z\core;

use z\lib\Basic;

/**
 * 模板机制
 *
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 *
 */
class Template
{
	private $data = array(); //数据栈
	
	/**
	 * 赋值到数据栈中
	 *
	 * @access public
	 * @param  string  $xKey      键名或键值对数组
	 * @param  string  $strValue  键值（$xKey为非数组时有效）
	 */
	public function assign($xKey, $strValue = ''){
		if(is_array($xKey)){
			foreach($xKey as $k => $v){
				$this->data[$k] = $v;
			}
		}
		else{
			$this->data[$xKey] = $strValue;
		}
	}

	/**
	 * 渲染模板
	 *
	 * @access public
	 * @param  string  $strViewName  视图名
	 */
	public function display($strViewName){
		$flag = Config::$options['php_cache_enable'];
		if($flag){
			//获取动态缓存文件
			$dynamic = Locafis::getc();
		}
		//若设置不使用动态缓存则强制读取并重新编译模板
		if(!$flag || !$dynamic){
			//读取模板文件
			$tplPath = Config::getViewPath($strViewName);
			//读取模板内容
			$content = Basic::read($tplPath);
			//编译模板内容
			$content = $this->complie($content);
			//保存动态缓存并获得其路径
			$dynamic = Locafis::savec($content);
		}
		//将数组变量导入到当前的符号表
		extract($this->data);
		//打开缓冲区
		ob_start();
		//载入模板
		require $dynamic;
		//返回缓冲内容并清空
		Response::setContent(ob_get_clean());
	}

	/**
	 * 编译模板内容
	 * @access public
	 * @param  sting   $strContent  模板内容
	 * @return string
	 */
	public function complie($strContent){
		$strContent = $this->parseWidget($strContent);
		$strContent = $this->parseLanguage('var', $strContent);
		$strContent = $this->parseLanguage('foreach', $strContent);
		$strContent = $this->parseLanguage('if', $strContent);
		$strContent = $this->parseLanguage('elseif', $strContent);
		$strContent = $this->parseLanguage('else', $strContent);
		$strContent = $this->parseLanguage('end', $strContent);
		return $strContent;
	}

	/**
	 * 解析部件
	 * @access private
	 * @param  sting    $strContent  模板内容
	 * @return string
	 */
	private function parseWidget($strContent){
		$pattern = array();
		preg_match_all('/\{\\$widgetView_([a-zA-Z]*)\}/', $strContent, $res);
		foreach($res[1] as $k => $v){
			$widgetPath = Config::getWidgetPath($v);
			$pattern[$res[0][$k]] = Basic::read($widgetPath);
		}
		return strtr($strContent, $pattern);
	}

	/**
	 * 解析模板语言
	 * @access private
	 * @param  sting    $strType     语言类型
	 * @param  sting    $strContent  模板内容
	 * @return string
	 */
	private function parseLanguage($strType, $strContent){
		switch($strType){
			case 'var' :
				$target = '/\{\\$.*?\}/';
				$action = 'replaceVar';
				break;
			case 'foreach' :
				$target = '/\{foreach.*?\}/i';
				$action = 'replaceForeach';
				break;
			case 'if' :
				$target = '/\{if.*?\}/i';
				$action = 'replaceIf';
				break;
			case 'elseif' :
				$target = '/\{elseif.*?\}/i';
				$action = 'replaceElseif';
				break;
			case 'else' :
				$target = '/\{else.*?\}/i';
				$action = 'replaceElse';
				break;
			case 'end' :
				$target = '/\{\/.*?\}/i';
				$action = 'replaceEnd';
				break;
		}
		preg_match_all($target, $strContent, $res);
		$pattern = array();
		if(!empty($res[0])){
			foreach($res[0] as $k => $v){
				// 组成替换数组
				$pattern[$v] = $this->$action($v);
			}
		}
		return strtr($strContent, $pattern);
	}

	/**
	 * 替换变量
	 * @access private
	 * @param  sting    $strContent  内容
	 * @return string
	 */
	private function replaceVar($strContent){
		//接着替换其中的变量
		$strContent = $this->publicReplaceVar($strContent);
		//替换为php代码
		$strContent = preg_replace('/\{(.*?)\}/', '<?php echo \\1; ?>', $strContent);
		return $strContent;
	}

	/**
	 * 替换foeach语句
	 * @access private
	 * @param  sting    $strContent  内容
	 * @return string
	 */
	private function replaceForeach($strContent){
		preg_match('/from=(\\$\S*)/i', $strContent, $from);
		preg_match('/key=([a-zA-Z_][a-zA-Z0-9_]*)/i', $strContent, $key);
		preg_match('/item=([a-zA-Z_][a-zA-Z0-9_]*)/i', $strContent, $item);
		//修正获得的字符串
		$from = $this->publicReplaceVar($from[1]);
		$key = trim(trim($key[1], '"'), "'");
		$item = trim(trim($item[1], '"'), "'");
		//组成替换数组
		return '<?php foreach(' . $from . ' as $' . $key . '=>$' . $item . ') { ?>';
	}

	/**
	 * 替换if语句
	 * @access private
	 * @param  sting    $strContent  内容
	 * @return string
	 */
	private function replaceIf($strContent){
		//替换为php代码
		$strContent = preg_replace('/\{if(.*?)\}/i', '<?php if(\\1) { ?>', $strContent);
		return $this->publicReplaceVar($strContent);
	}

	/**
	 * 替换elseif语句
	 * @access private
	 * @param  sting    $strContent  内容
	 * @return string
	 */
	private function replaceElseif($strContent){
		//替换为php代码
		$strContent = preg_replace('/\{elseif(.*?)\}/i', '<?php } elseif(\\1) { ?>', $strContent);
		return $this->publicReplaceVar($strContent);
	}

	/**
	 * 替换elseif语句
	 * @access private
	 * @param  sting    $strContent  内容
	 * @return string
	 */
	private function replaceElse($strContent){
		return preg_replace('/\{else(.*?)\}/i', '<?php } else { ?>', $strContent);
	}

	/**
	 * 替换结束语句
	 * @access private
	 * @param  sting    $strContent  内容
	 * @return string
	 */
	private function replaceEnd($strContent){
		//替换为php代码
		return preg_replace('/\{\/.*?\}/i', '<?php } ?>', $strContent);
	}

	/**
	 * 替换变量
	 * @access private
	 * @param  sting    $strContent  内容
	 * @return string
	 */
	private function publicReplaceVar($strContent){
		//{$value.key}
		$strContent = preg_replace('/\\$([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)/', "\$\\1['\\2']", $strContent);
		//{$value.number}
		$strContent = preg_replace('/\\$([a-zA-Z_][a-zA-Z0-9_]*)\.(\d*)/', "\$\\1[\\2]", $strContent);
		return $strContent;
	}

}
