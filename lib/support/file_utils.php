<?php
	/**
	* @package Support
	*/
	class FileUtils {
		
		/**
		* Creates paths to a file that is OS generic
		* @uses FileUtils::join('root', 'sub', 'nimble.txt')
		*/
		public static function join() {
			$args = func_get_args();
			return join(DIRECTORY_SEPARATOR, $args);
		}
	  
		/**
		* Recursively creates directories
		* @uses FileUtils::mydir_p('myapp', 'controller') 
		* @param string $path - Path to create directory
		* @param integer $mode - Mode in which to create directories
		*/
		public static function mkdir_p($path, $mode=0775) {
			if(!is_dir($path)) {
				mkdir($path, $mode, true);
			}
		}
	
	}

?>