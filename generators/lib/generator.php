<?php

$folder = dirname(__FILE__);

define('TEMPLATE_PATH', $folder . '/../templates');

 class Generator {
	
	/**
	* @param $path Path to creat file
	* @param $env Enviroment name
	*/
	public static function database_config($path, $env) {
		$db = fopen($path, "w");
		fwrite($db, preg_replace('/\[env\]/', $env, file_get_contents(TEMPLATE_PATH . DIRECTORY_SEPARATOR . 'database.json')));
		fclose($db);
	}
	
	public static function boot($path) {
		copy(TEMPLATE_PATH . DIRECTORY_SEPARATOR . 'boot.php.tmpl', $path);
	}
	
	
 }

?>