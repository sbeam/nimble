<?php

class FileUtils {
	
	/**
	* @uses FileUtils::join('root', 'sub', 'nimble.txt')
	*/
	public static function join() {
		$args = func_get_args();
		return join(DIRECTORY_SEPARATOR, $args);
	}
	
}

?>