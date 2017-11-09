<?php

const TMPFS_CACHE_DIR = 'cache'; //存放缓存文件的目录
const TMPFS_SHORT_URL_DIR = 'surlmaps'; //存放短地址的目录

//默认路由模式，协议名://主机名/模块名称(index时省略)/入口名-控制器名称-key-value-key-value... + 任意后缀名
const DEFAULT_ROUTER_MODEL = 0;
//短地址路由模式，协议名://主机名/六位字符串 + 任意后缀名
const SHORTURL_ROUTER_MODEL = 1;
//目录路由模式，协议名://主机名/模块名称/入口名/控制器名称/key/value/key/value...
const DIRECTORY_ROUTER_MODEL = 2;

const AUTHOR_KEY = 'zhiYeTan'; //作者密钥，作为加密的salt
const BASE_CHAR_MAP = '0aAbBcC1dDeEfF2gGhHiI3jJkKlL4mMnNoO5pPqQrR6sStTuU7vVwWxX8yYzZ9'; //由大小写字母和数字组成的基本字符表

const ERRNO_NONE = 0;//没有发生错误

const ERR_ENTRY_NOT_EXIST = '未开放的传送门';
const ERR_CONTROLLER_NOT_EXIST = '待充能的魔法节点';
const ERR_CONTROLLER_METHOD_NOT_EXIST = '法术还没有准备好';
const ERR_ILLEGAL_PARAMETER = '魔力成份异常';

const CACHE_TYPE_STATIC = 0; //静态缓存类型
const CACHE_TYPE_DYNAMIC = 1; //动态缓存类型
const CACHE_TYPE_DATA = 2; //数据缓存类型

const TEMPLATE_SUFFIX = '.tpl'; //模板后缀
const WIDGET_SUFFIX = '.mdl'; //部件后缀

const UNIFIED_LOG_PATH = UNIFIED_PATH . 'logs' . Z_DS; //日志存放路径
const LOG_MAX_SIZE = 1000000; //日志文件大小上限，单位字节




