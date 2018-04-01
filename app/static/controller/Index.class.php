<?php

class appConIndexCls extends zCoreDataControllerCls
{
	public function main(){
		//要过滤的文件
		$filterFiles = [
			'README.txt'
		];
		$this->assign('list', zPubBasicLib::listDirTree(UNIFIED_PATH . 'resource', $filterFiles));
	}
}
