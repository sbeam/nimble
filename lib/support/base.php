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
	

	/**
	* Similar to rubys collect method
	* @param function $func
	* @param array|interator $array
	* @uses collect(function($value){return $value+1}, range(1,5));
	*/
	function collect($func, $array) {
		$out = array();
		foreach($array as $value) {
			array_push($out, $func($value));
		}
		return $out;
	}

?>