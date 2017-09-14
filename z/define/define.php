<?php

//默认路由模式，协议名://主机名/模块名称(index时省略)/入口名-控制器名称-key-value-key-value... + 任意后缀名
const DEFAULT_ROUTER_MODEL = 0;
//短地址路由模式，协议名://主机名/模块名称(index时省略)/六位字符串 + 任意后缀名
const SHORTURL_ROUTER_MODEL = 1;
//目录路由模式，协议名://主机名/入口名/模块名称/控制器名称/key/value/key/value...
const DIRECTORY_ROUTER_MODEL = 2;

const SHORT_URL_DIR = 'maps'; //存放短地址的目录
const AUTHOR_KEY = 'zhiYeTan'; //作者密钥，作为加密的salt
const BASE_CHAR_MAP = '0aAbBcC1dDeEfF2gGhHiI3jJkKlL4mMnNoO5pPqQrR6sStTuU7vVwWxX8yYzZ9'; //由大小写字母和数字组成的基本字符表

const ERR_ENTRY_NOEXIST = '你正在访问一个不存在的应用入口!';
const ERR_MODULE_NOEXIST = '你正在访问一个不存在的应用模块!';
const ERR_CONTROLLER_NOEXIST = '你正在访问一个不存在的控制节点!';

const TEMPLATE_SUFFIX = '.tpl'; //模板后缀
const WIDGET_SUFFIX = '.mdl'; //部件后缀

const UNIFIED_LOG_PATH = UNIFIED_PATH . 'logs' . Z_DS; //日志存放路径
const LOG_MAX_SIZE = 1000000; //日志文件大小上限，单位字节




