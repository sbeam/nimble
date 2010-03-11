<?php
	/**
	* @package Support
	* Loads in all support classes
	*/
require_once(dirname(__FILE__) . '/file_utils.php');

# $available_helpers = array('tag_helper', 'mime', 'inflector', 'string_cacher', 'asset_tag', 'cycler');
#
function nimble_load_helper($name) {
    require_once(FileUtils::join(dirname(__FILE__), $file . '.php'));
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
