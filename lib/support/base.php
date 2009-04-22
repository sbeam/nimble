<?php
	/**
	* @package Support
	* Loads in all support classes
	*/
	$dir = dirname(__FILE__);
	require_once($dir . '/file_utils.php');
	foreach(array('tag_helper', 'mime', 'inflector', 'string_cacher', 
				  'asset_tag', 'cycler') as $file) {
		require_once(FileUtils::join($dir, $file . '.php'));
	}
	

	

?>