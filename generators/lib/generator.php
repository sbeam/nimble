<?php

	/**
	* This File contains all the logic for creating the nimble MVC skeleton
	*/


	require_once(dirname(__FILE__) . '/../../lib/support/file_utils.php');
	require_once(dirname(__FILE__) . '/../../lib/support/file_utils.php');
	$folder = dirname(__FILE__);

	define('TEMPLATE_PATH', FileUtils::join($folder, '..', 'templates'));
	define('SCRIPT_PATH', FileUtils::join($folder, '..', '..', 'bin'));

	/**
	* @package Generators
	*/
	class Generator {

		/**
		* Creates the database config files for a specified enviromant
		* @param string $path - Path to creat file
		* @param string $env - Enviroment name
		*/
		public static function database_config($path, $env) {
			$db = fopen($path, "w");
			fwrite($db, preg_replace('/\[env\]/', $env, file_get_contents(TEMPLATE_PATH . DIRECTORY_SEPARATOR . 'database.json')));
			fclose($db);
		}
	
		/**
		* Creates a boot.php file
		* @param string $path - path you want to place the boot file
		*/
		public static function boot($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'boot.php.tmpl'), $path);
		}
	
		/**
		* Creates a .htaccess file
		* @param string $path - path you want the htaccess stored
		*/
		public static function htaccess($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'htaccess.tmpl'), $path);
		}

		/**
		* Creates the script directory and copies everything from bin except nimblize
		* @param string $path - path you want the script folder located
		*/
		public static function scripts($path) {
			if($dir = opendir(SCRIPT_PATH)){
				while (($file = readdir($dir)) !== false) {
					if($file == 'nimblize' || $file == '.' || $file == '..') {
						continue;
					}
					copy(FileUtils::join(SCRIPT_PATH, $file), FileUtils::join($path, $file));
				}
			}
		}

		/**
		* Creates an empty route file
		* @param string $path
		*/
		public static function route($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'route.tmpl'), $path);
		}
	
		/**
		* Create a r404 class file 
		* @param string $path - path you want file created
		*/
		public static function r404($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'r404.tmpl'), $path);
		}
		/**
			* Creates a Nimble Unit Test Case
			* @param string $name name of test
			*/
				
		public static function unit_test($name) {
			$class_name = Inflector::classify($name);
			$path = FileUtils::join(NIMBLE_ROOT, 'test', 'unit');
			
			$string = "<?php \n";
			$string .= "	/**\n	* @package unit_test\n	*	*/\n";
			$string .= "  class {$class_name} extends NimblePHPUnitTestCase";
			$string .= " { \n\n";
			$string .= "  }\n";
			$string .= "?>";
			
			FileUtils::mkdir_p($path);
			$file = fopen(FileUtils::join($path, $class_name . 'Test.php'), "w");
			fwrite($file, $string);
			fclose($path);
		}
		
		/**
			* Creates a model class with option of a parent to extend
			* @param string $name the name of the class
			* @param string $parent the parent class you with to extend with this model
			*
			*/
		public static function model($name, $parent='') {
			$class_name = Inflector::classify($name);
			$path = FileUtils::join(NIMBLE_ROOT, 'app', 'model', $class_name . '.php');
			$string = "<?php \n";
			$string .= "	/**\n	* @package model\n	*	*/\n";
			$string .= "  class {$class_name}"; 
			if(!empty($parent)) {
				$string .= "extends $parent";
			}
			$string .= " { \n\n";
			$string .= "  }\n";
			$string .= "?>";
			
			$file = fopen($path, "w");
			fwrite($file, $string);
			fclose($path);
			
		}

		/**
			* Creates a controller and its associated views ex. add, create, update, show, index, delete
			* @todo need to add name space support
			* @param string $name - suffix you want the name the controller
			*/
		public static function controller($name) {
			$class_name = Inflector::classify($name);
			$path_name = FileUtils::join(NIMBLE_ROOT, 'app', 'controller', $class_name . 'Controller.php');
			$view_path = FileUtils::join(NIMBLE_ROOT, 'app', 'view', strtolower(Inflector::underscore($class_name)));
			FileUtils::mkdir_p($view_path);
			$string = "<?php \n";
			$string .= "	/**\n	* @package controller\n	*	*/\n";
			$string .= "  class {$class_name}Controller extends Controller { \n";
			$string .= self::create_view_functions($view_path);
			$string .= "  }\n";
			$string .= "?>";
	
			$db = fopen($path_name, "w");
			fwrite($db, $string);
			fclose($db);
		}

		/**
		* @access private
		* @return string
		* @see function controller
		* @param string $view_path - folder in which to create files
		*/
		private static function create_view_functions($view_path) {
			$out = '';
			foreach(array('index', 'add') as $view) {
				self::view(FileUtils::join($view_path, $view . '.php'));
				$out .= self::view_function($view);
			}
	
			foreach(array('create') as $view){
				$out .= self::view_function($view);
			}
	
	
			foreach(array('update', 'delete') as $view) {
				$out .= self::view_function($view, true);
			}
	
	
			foreach(array('show', 'edit') as $view) {
				self::view(FileUtils::join($view_path, $view . '.php'));
				$out .= self::view_function($view, true);
			}
			return $out;
		}
	
		/**
		* @access private
		* @param string $action - name of the action
		* @param boolean $id - wither created function takes an id or not
		* @see function create_view_functions
		* @return string 
		*/
		private static function view_function($action, $id=false) {
			$out = "	/**\n";
			$out .= "	* " . $action . "\n";
			if($id){
			$out .= "	* @param " . '$id' . " string\n";
			$out .= "	*/\n";
			$out .= "    public function " . $action . '($id)' . " {\n";
			}else{
			$out .= "	*/\n";
			$out .= "    public function " . $action . "() {\n";
			}
			$out .= "    }\n";
			$out .= "\n";
			return $out;
		}
	
		/**
		* Creates a view template file
		* @see function create_view_functions
		* @param string $path - path to touch the file
		*/
		private static function view($path) {
			touch($path);
		}

	}

	?>