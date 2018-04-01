<?php

class appConUploadCls extends zCoreDataControllerCls
{
	protected $getAllowedKeys = ['dir', 'filename'];
	protected $getValidationRules = [
		//'dir'		=> '/^[0-9a-zA-Z-_\/]*$/',
		//'filename'	=> '/^[0-9a-zA-Z-_]*$/'
	];
	protected $getFilterRules = [
		['dir', 'public', '/[^0-9a-zA-Z-_\/]/'],
		['filename', '', '/[^0-9a-zA-Z-_]/']
	];
	
	public function main(){
		echo '<pre>';
		print_r($_GET);
	}
}
