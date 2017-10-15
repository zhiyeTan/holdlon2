<?php
namespace z\core;

use z\lib\Basic;

/**
 * 路由策略
 * 使用URL重写规则后，到达php-fpm的URL将形如："协议名://主机名/index.php?s=..."
 * 包括以下3种路由模式：
 * DEFAULT_ROUTER_MODEL => 协议名://主机名/模块名称(index时省略)/入口名-控制器名称-key-value-key-value...
 * SHORTURL_ROUTER_MODEL => 协议名://主机名/六位字符串
 * DIRECTORY_ROUTER_MODEL => 协议名://主机名/模块名称/入口名/控制器名称/key/value/key/value...
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class Router{
	private static $mapsPath; //短地址映射所在路径
	private function __construct(){}//静态类，不允许实例化
	
	/**
	 * 创建Url
	 * 
	 * @access public
	 * @param  array   $arrParam    参数键值对数组
	 * @param  int     $intPattern  路由模式
	 * @param  string  $strDomain   完整域名（包含协议部分）
	 * @param  string  $strSuffix   后缀名
	 * @return string
	 */
	public static function mkUrl($arrParam, $intPattern = DEFAULT_ROUTER_MODEL, $strDomain = '', $strSuffix = 'html'){
		$url = $strDomain ?? '';
		$url = trim($url, '/');
		Config::correctBasicUrlParamArray($arrParam);
		switch($intPattern){
			case SHORTURL_ROUTER_MODEL: //短地址模式
				$queryStr = http_build_query($arrParam);
				$hashStr = md5(AUTHOR_KEY . $queryStr);
				//将加密串分成4段计算
				for($i = 0; $i < 4; $i++){
					//将截取每段字符并转为10进制数组，再与0x3fffffff做位与运算（即把30位以后的字符归零）
					$idx = hexdec(substr($hashStr, $i << 2, 4)) & 0x3fffffff;
					//生成6位短链接
					$tmpStr = '';
					for($j = 0; $j < 6; $j++){
						//与$basechar的最大下标0x0000003d（即61）做位与运算得到新的数组下标后取得对应的值
						$tmpStr .= BASE_CHAR_MAP[$idx & 0x0000003d];
						$idx = $idx >> 5;
					}
					//构造映射路径
					$tmpFilePath = self::getMapPath() . $tmpStr;
					$map = Basic::read($tmpFilePath);
					//如果不存在映射或已存在映射，跳出循环
					if(!$map || $map === $queryStr){
						break;
					}
				}
				//如果不存在映射，建立映射
				if(!$map){
					Basic::mkFolder(self::getMapPath());
					Basic::write($tmpFilePath, $queryStr, false);
				}
				$url .= $tmpStr;
				break;
			case DIRECTORY_ROUTER_MODEL: //目录型模式
				$url .= '/' . $arrParam['m'] . '/' . $arrParam['e'] . '/' . $arrParam['c'] . '/';
				unset($arrParam['e'], $arrParam['m'], $arrParam['c']);
				$url .= strtr(http_build_query($arrParam), '=&', '/');
				$url  = rtrim($url, '/');
				break;
			default: //默认模式
				$url .= '/' . ($arrParam['m'] == 'index' ? '' : $arrParam['m'] . '/');
				$url .= $arrParam['e'] . '-' . $arrParam['c'] . '-';
				unset($arrParam['m'], $arrParam['e'], $arrParam['c']);
				$url .= strtr(http_build_query($arrParam), '=&', '-');
				$url  = trim($url, '-');
		}
		$url .= $strSuffix ? ('.' . $strSuffix) : '';
		return $url;
	}

	/**
	 * 解析请求
	 * 不受路由模式影响，以适应不同场景
	 * 
	 * @access public
	 * @param  string  $strRequest      请求的查询字符串
	 * @param  bool    $boolReplaceGET  是否替换$_GET参数
	 * @return array
	 */
	public static function parse($strRequest, $boolReplaceGET = true){
		$arrRequest = [];
		if($strRequest){
			//先移除无关紧要的后缀名
			if($idx = strpos($strRequest, '.')){
				$strRequest = substr($strRequest, 0, $idx);
			}
			//首先判断以何种模式处理
			$intPattern = strpos($strRequest, '-') ? DEFAULT_ROUTER_MODEL 
						: (strpos($strRequest, '/') ? DIRECTORY_ROUTER_MODEL : SHORTURL_ROUTER_MODEL);
			switch ($intPattern){
				case SHORTURL_ROUTER_MODEL: //短地址模式
					$filePath = self::getMapPath() . $strRequest;
					$data = Basic::read($filePath);
					if($data !== false){
						parse_str($data, $tmpQueryArr);
						foreach($tmpQueryArr as $k => $v){
							$arrRequest[$k] = $v;
						}
					}
					break;
				case DIRECTORY_ROUTER_MODEL: //目录型模式
					$tmpArr = explode('/', trim($strRequest, '/'));
					$arrRequest = self::switchArray($tmpArr);
					break;
				default: //默认模式
					$strRequest = trim($strRequest, '-');
					$strRequest = strpos($strRequest, '/') ? str_replace('/', '-', $strRequest) : ('index-' . $strRequest);
					$tmpArr = explode('-', $strRequest);
					$arrRequest = self::switchArray($tmpArr);
			}
		}
		if($boolReplaceGET){
			self::replaceGET($arrRequest);
		}
		else{
			return $arrRequest;
		}
	}
	
	/**
	 * 转换数组
	 * 
	 * @access private
	 * @param  array     $arrQuest   要转换的数组
	 * @return array
	 */
	private static function switchArray($arrQuest){
		$res = [];
		$tmpArr = array('m', 'e', 'c');
		foreach($arrQuest as $k => $v){
			if(!empty($tmpArr[$k])){
				$res[$tmpArr[$k]] = $v;
				continue;
			}
			if($k % 2 == 1){
				$tk = $k + 1;
				$res[$v] = empty($arrQuest[$tk]) ? '' : $arrQuest[$tk];
			}
		}
		return $res;
	}
	
	/**
	 * 获取短地址映射路径
	 * 
	 * @access private
	 * @return path
	 */
	private static function getMapPath(){
		if(!self::$mapsPath){
			self::$mapsPath = Config::getAppPathByTmpfs(TMPFS_SHORT_URL_DIR, false);
		}
		return self::$mapsPath;
	}
	
	/**
	 * 替换$_GET为指定键值对数组
	 * 
	 * @access private
	 */
	private static function replaceGET($arrTarget){
		unset($_GET); //清空，同时可简单地过滤一些注入
		Config::correctBasicUrlParamArray($arrTarget);
		$_GET = $arrTarget;
	}
}
