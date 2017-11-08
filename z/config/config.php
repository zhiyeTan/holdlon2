<?php

return [
	
	//错误提示模式(0友好提示，1明细提示)
	'tips_mode'				=> 0,
	
	//静态资源的域名
	'static_domain'			=> 'http://s.tzy.com',
	
	//静态资源后缀名(以|分割)
	'static_suffix'			=> 'js|css|jpg|png|bmp|gif',
	
	//服务器数据缓存有效期(单位s) [小于0表示不使用，0表示永久，大于0表示指定值]
	'data_cache_expire'		=> -1,
	
	//服务器静态缓存有效期(单位s) [小于0表示不使用，0表示永久，大于0表示指定值]
	'html_cache_expire'		=> 0,
	
	//是否启用服务器动态缓存 [0否，1是]
	'php_cache_enable'		=> 0,
	
	//客户端本地缓存时间(单位s) 过期前不会重复请求服务器 [使用在header表头中]
	'local_expire'			=> 0,
	
	//默认时区
	'default_timezone'		=> 'PRC',
	
	//默认语言（响应体的语言）
	'default_lang'			=> 'zh-cn',
	
	//设置session有效时间(单位s)
	'session_expire'		=> 10800,
	
	//设置cookie有效时间(单位s)
	'cookie_expire'			=> 10800
	
];