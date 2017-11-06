<?php

return [
	
	//错误提示模式(0友好提示，1明细提示)
	'tips_mode'				=> 0,
	
	//入口文件名与应用位置文件夹名的值对映射
	'entry_maps'			=> [
								'index'		=> 'app',
								'admin'		=> 'admin'
							],
	//静态资源的域名
	//TODO 做一个统一的静态资源管理器，部署在静态目录下
	//TODO 同时做针对性的nginx/Apache设置，不允许运行非管理器外的非静态文件
	//TODO 由于多站点调用资源的不可预估性，不提供删除操作
	'static_domain'			=> 'http://static.tzy.com',
	
	//静态资源后缀名(以'|'分割)
	'static_suffix'			=> 'jpg|png|bmp|gif',
	
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