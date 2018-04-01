<?php

//[ 应用入口文件 ]

//定义当前应用的默认目录(即index入口的映射目录，非index时，入口名与目录名一致)
const APP_DEFAULT_DIR = 'default';

//定义当前应用可访问的应用目录列表(空数组表示无限制)
const APP_ALLOW_DIR = [];

//加载框架引导文件
require dirname(__DIR__) . '/z/z.php';