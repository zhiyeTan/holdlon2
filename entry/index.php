<?php

// [ 应用入口文件 ]
// 定义分隔符
const Z_DS = DIRECTORY_SEPARATOR;

// 定义入口目录(多点部署时不需要定义)
const ENTRY_PATH = __DIR__;

// 定义统一的路径
define('UNIFIED_PATH', dirname(ENTRY_PATH) . Z_DS);

// 加载框架引导文件
require UNIFIED_PATH . Z_DS . 'z' . Z_DS . 'z.php';