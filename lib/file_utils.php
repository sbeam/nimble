<?php

class FileUtils {
	
	
	public static function join() {
		$args = func_get_args();
		return join(DIRECTORY_SEPARATOR, $args);
	}
	
}

?>