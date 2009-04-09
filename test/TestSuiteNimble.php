
<?php

	$classes_loaded = array();
	foreach (glob("*.php") as $file) {
		if (realpath($file) !== realpath(__FILE__)) {
			require_once($file);
			$classes_loaded[] = pathinfo($file, PATHINFO_FILENAME);
		}
	}

	/**
	* @package FrameworkTest
	*/
	class TestNimble {
		public static function suite() {
			global $classes_loaded;
			$suite = new PHPUnit_Framework_TestSuite();
			foreach ($classes_loaded as $class) {
				if(file_exists($class . '.php')){
					$suite->addTestSuite(new ReflectionClass($class));
				}
			}
			return $suite;
		}
	}

?>
