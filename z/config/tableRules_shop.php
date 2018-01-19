<?php
/**
 * 表名与表名规则、分区规则、表数据类型的映射关系
 */
return [
	//原始表名 => [表名规则, 分区规则, 表数据类别(对应储存在redis上的类型标记，默认空，对应redis的default)]
	'order'		=> [MAP_RULE_MONTH, MAP_RULE_MONTH],
];