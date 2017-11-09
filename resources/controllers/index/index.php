<?php

namespace resources\controllers\index;

use \z\core\{
	Controller,
	Response
};
use \z\lib\Basic;

class index extends Controller
{
	public function main(){
		//要过滤的文件
		$filterFiles = [
			'controllers', 'README.txt', 'robots.txt', 'favicon.ico', 'index.php', '.htaccess'
		];
		$list = Basic::listDirTree(UNIFIED_PATH . APP_DEFAULT_DIR, $filterFiles);
		Response::init()
		->setContentType('json')
		->setContent($list);
	}
}


