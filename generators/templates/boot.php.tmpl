<?php
	
	/**
	* This file boots and loads the framework
	* In order for the enviroments to work correctly add the line below to your server apache config
	* depending on which enviroment you want to load replace <enviroment> with development | production | test | staging | etc.
	* note this can also be done at the vhost level so you cna run multiple enviroments on one machine.
	* If you are on shared hosting ignore this and just uncomment the $_SERVER['WEB_ENVIRONMENT'] = <enviroment> line below
	*  # Set an environment variable for nimble
	*  SetEnv WEB_ENVIRONMENT <enviroment>
	*/
	
	//$_SERVER['WEB_ENVIRONMENT'] = 'development';
	//$_SERVER['WEB_ENVIRONMENT'] = 'test';
	//$_SERVER['WEB_ENVIRONMENT'] = 'staging';
	//$_SERVER['WEB_ENVIRONMENT'] = 'production';

	define('NIMBLE_ENV', $_SERVER['WEB_ENVIRONMENT']);
	function load_files($dir) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(preg_match('/\.php$/' , $file)) {
					require_once($dir . DIRECTORY_SEPARATOR . $file);
				}
			}
			closedir($dh);
		}
	}
	/** load nimble */
	require_once('nimble/nimble.php');
	/** load controlers  and models */
	$dirs = array('controller', 'model');
	foreach($dirs as $dir) {
		load_files(join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'app', $dir)));
	}
	// load any custom global config options 		
	require_once('config.php');
	// load any custom enviroment config options
	// Nimble::Log('loading ' . NIMBLE_ENV . ' enviroment');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . NIMBLE_ENV . DIRECTORY_SEPARATOR . 'config.php');
	
	
	
	Run();
?>