<?php

class FileUtils {
	
	/**
	* @uses FileUtils::join('root', 'sub', 'nimble.txt')
	*/
	public static function join() {
		$args = func_get_args();
		return join(DIRECTORY_SEPARATOR, $args);
	}
  
  /**
  *
  * @param $path string Path to create directory
  */
  public static function mkdir_p($path, $mode=0777) {
    mkdir($path, $mode, true);
  }
	
}

?>