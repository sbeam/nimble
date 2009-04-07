<?php
require_once(dirname(__FILE__) . '/../../lib/support/file_utils.php');
require_once(dirname(__FILE__) . '/../../lib/support/file_utils.php');
$folder = dirname(__FILE__);

define('TEMPLATE_PATH', FileUtils::join($folder, '..', 'templates'));
define('SCRIPT_PATH', FileUtils::join($folder, '..', '..', 'bin'));

 class Generator {
	
	/**
	* @param $path Path to creat file
	* @param $env Enviroment name
	*/
	public static function database_config($path, $env) {
		$db = fopen($path, "w");
		fwrite($db, preg_replace('/\[env\]/', $env, file_get_contents(TEMPLATE_PATH . DIRECTORY_SEPARATOR . 'database.json')));
		fclose($db);
	}
	
	public static function boot($path) {
		copy(FileUtils::join(TEMPLATE_PATH, 'boot.php.tmpl'), $path);
	}
	
	public static function htaccess($path) {
		copy(FileUtils::join(TEMPLATE_PATH, 'htaccess.tmpl'), $path);
	}
	
	
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
	
	
	public static function route($path) {
		copy(FileUtils::join(TEMPLATE_PATH, 'route.tmpl'), $path);
	}
	
	public static function r404($path) {
		copy(FileUtils::join(TEMPLATE_PATH, 'r404.tmpl'), $path);
	}
	
	
	public static function controller($name) {
		$class_name = Inflector::classify($name);
		$path_name = FileUtils::join(NIMBLE_ROOT, 'app', 'controller', $class_name . 'Controller.php');
		$view_path = FileUtils::join(NIMBLE_ROOT, 'app', 'view', strtolower(Inflector::underscore($class_name)));
		FileUtils::mkdir_p($view_path);
		$string = "<?php \n";
		$string .= "  class $class_name extends Controller { \n";
		$string .= self::create_view_functions($view_path);
		$string .= "  }\n";
		$string .= "?>";
		
		$db = fopen($path_name, "w");
		fwrite($db, $string);
		fclose($db);
	
	
	}
	
	
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
	
	private static function view_function($view, $id=false) {
		$out = "	/**\n";
		$out .= "	* " . $view . "\n";
		if($id){
		$out .= "	* @param " . '$id' . " string\n";
		$out .= "	*/\n";
		$out .= "    public function " . $view . '($id)' . " {\n";
		}else{
		$out .= "	*/\n";
		$out .= "    public function " . $view . "() {\n";
		}
		$out .= "    }\n";
		$out .= "\n";
		return $out;
	}
	
	public static function view($path) {
		touch($path);
	}
	
	public static function model($name) {
	
	}
	
	
 }

?>