<?php

// [ 应用入口文件 ]
// 定义分隔符
const Z_DS = DIRECTORY_SEPARATOR;

//当前应用的默认目录(即index入口的映射目录，非index时，入口名与目录名一致)
const APP_DEFAULT_DIR = 'app';

// 定义统一的路径
define('UNIFIED_PATH', dirname(__DIR__) . Z_DS);

// 加载框架引导文件
require UNIFIED_PATH . Z_DS . 'z' . Z_DS . 'z.php';