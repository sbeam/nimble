<?php
	/**
	* @package Support
	* Loads in necessary support classes
	*/
require_once(dirname(__FILE__) . '/file_utils.php');
require_once(dirname(__FILE__) . '/inflector.php');

# $available_helpers = array('tag_helper', 'mime', 'string_cacher', 'asset_tag', 'cycler');
#
function nimble_load_helper($name) {
    require_once(FileUtils::join(dirname(__FILE__), $name . '.php'));
}

