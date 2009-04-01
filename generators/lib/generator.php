<?php
require_once(dirname(__FILE__) . '/../../lib/support/file_utils.php');
$folder = dirname(__FILE__);

define('TEMPLATE_PATH', $folder . '/../templates');
define('SCRIPT_PATH', $folder . '/../../bin');

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
		echo SCRIPT_PATH;
		if($dir = opendir(SCRIPT_PATH)){
			while(($file = readdir($dir) !== false)) {
				if($file == 'nimblize' || $file == '.' || $file == '..') {
					continue;
				}
				echo FileUtils::join($path, $file);
				copy($file, FileUtils::join($path, $file));
			}
		}
	}
	
	
	public static function controller($name) {
		$class_name = Inflector::classify($name);
		$path_name = $name;
		$view_path = FileUtil::join(NIMBLE_ROOT, 'app', 'view', $path_name);
		$string = "<?php \n";
		$string .= "  $class_name extends Controller { \n";
		$string .= self::create_view_functions($view_path);
		$string .= "  }";
		$string .= "?>";
		
	
	
	}
	
	
	private static function create_view_functions($view_path) {
		$out = '';
		foreach(array('index', 'create') as $view) {
			self::view(FileUtils::join($view_path, $view . '.php'));
			$out .= self::view_function($view);
		}
		foreach(array('update', 'show', 'delete') as $view) {
			self::view(FileUtils::join($view_path, $view . '.php'));
			$out .= self::view_function($view, true);
		}
		return $out;
	}
	
	private static function view_function($view, $id=false) {
		$out = '/**';
		$out .= '* ' . $view;
		if($id){
		$out .= '* @param $id string';
		$out .= "*/";
		$out .= '    public function ' . $view . '($id) {';
		}else{
		$out .= "*/";
		$out .= '    public function ' . $view . '() {';
		}
		$out .= "    }";
		return $out;
	}
	
	public static function view($path) {
		touch($path);
	}
	
	public static function model($path, $name) {
	
	}
	
	public function route($path) {
	
	}
	
	public function app_config($path) {
	
	}
	
	public function env_config($path, $env) {
	
	}
	
	
 }

?>