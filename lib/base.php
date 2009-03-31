<?php
require_once(dirname(__FILE__) . '/controller.php');
require_once(dirname(__FILE__) . '/exception.php');
require_once(dirname(__FILE__) . '/helper.php');
require_once(dirname(__FILE__) . '/route.php');

/**
 * Nimble is the base class in the application.
 * This class provides methods to add & call routes, parse URLs, and load plugins.
 * @package Nimble
 */
class Nimble
{
    var $routes = array();
    var $config = array();
    var $plugins = array();
    static private $instance = NULL;

    function __construct()
    {
        $this->url = (isset($_GET['url'])) ? trim($_GET['url'], '/') : '';

        if(!isset($this->uri)) {
            $this->uri = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
        }
        $this->load_plugins();
    }

    /**
     * Get the global Nimble object instance.
     * @return Nimble The global Nimble reference.
     */
    public static function getInstance()
    {
        if(self::$instance == NULL) {
            self::$instance = new Nimble();
        }
        return self::$instance;
    }

    /**
     * Add a URL and associated controller class, method, and HTTP method to the routing table.
     * @param string $url The URL pattern for this rule.
     * @param string $this->klass The name of the controller class to instantiate when this rule matches.
     * @param string $this->klass_method The name of the method on the instantiated controller class.
     * @param string $http_method The HTTP method this request responds to.
     */
    public function add_url($rule, $klass, $klass_method, $http_method = 'GET')
    {
        // parse format
        $has_format = false;
        if(preg_match('/\.[a-zA-Z0-9]+$/', $this->url)) {
            $rule .= '\.(?P<format>[a-zA-Z0-9]+)';
            $has_format = true;
        }
        $rule = preg_replace('/:([a-zA-Z0-9_]+)(?!:)/', '(?P<\1>[a-zA-Z0-9_-]+)', $rule);
        $this->routes[] = array('/^' . str_replace('/','\/',$rule) . '$/', $klass, $klass_method, $http_method, $has_format);
    }

    /**
     * Match the HTTP request's URL and HTTP method against the stored routes and, if a match is found, call the appropriate controller's method.
     * If the client you're using doesn't support sending HTTP requests with methods
     * other than GET or POST, set $_POST['_method'] to the actual HTTP method you wish to use.
     */
    public function dispatch($test=false)
    {	
      foreach($this->routes as $rule=>$conf) {
        // if a vaild _method is passed in a post set it to the REQUEST_METHOD so that we can route for DELETE and PUT methods
        if(isset($_POST['_method']) && !empty($_POST['_method']) && in_array(strtoupper($_POST['_method']), Route::$allowed_methods)){
            $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
        }

            // test to see if its a valid route
        if (preg_match($conf[0], $this->url, $matches) && $_SERVER['REQUEST_METHOD'] == $conf[3]){
            // Only declared variables in URL regex
            $matches = $this->parse_urls_args($matches);
            $this->klass = new $conf[1]();

            $this->klass->format = ($conf[4]) ? array_pop($matches) : 'html';

            ob_start();

            // call before filters
            call_user_func(array($this->klass, "run_before_filters"), $conf[2]);

            // call methods on controller class
            call_user_func_array(array($this->klass , $conf[2]), $matches);
				
        if(!$this->klass->has_rendered && isset($this->config['view_path'])) {
          /**
          * Add inflector for this type of code from now on
          */
          $dir = preg_replace('/Controller/', '', $conf[1]);
          $dir = strtolower($dir);
          $view = join(DIRECTORY_SEPARATOR ,array($this->config['view_path'], $dir, $conf[2] . '.php'));
          $this->klass->render($view);
        }
				
        // call after filters
        call_user_func(array($this->klass, "run_after_filters"), $conf[2]);

        $out = ob_get_contents();
        ob_end_clean();  
        if (count($this->klass->headers)>0){
          foreach($this->klass->headers as $header){
             header($header);
          }
        } 
        print $out;
        if(!$test){
          exit();
        }
      }
    }

        if(empty($_SERVER['REQUEST_METHOD'])){
          throw new NimbleExecption('No Request Paramater');
        }
		if(!$test){
			call_user_func(array('r404' , $_SERVER['REQUEST_METHOD'])); 
		}		
    }

    /**
     * Parse an array of URL parts from preg_match for allowed matches.
     * Nimble uses named subpatterns to identify parts of the URL to pull.
     * This function filters out any non-named subpatterns (indexed by a number)
     * and returns only matches that are indexed by a string.
     * @param array $matches The list of URL matches from preg_match.
     * @return array The list of valid named subpattern matches.
     */
    private function parse_urls_args($matches)
    {
      $first = array_shift($matches);
      $new_matches = array();
      foreach($matches as $k=>$match){
        if (is_string($k)){
          $new_matches[$k]=$match;
        }
      }
      return $new_matches;
    }

    /**
     * Add a list of plugins to be loaded when the Nimble object is instantiated.
     * This method can be called either globally or at the controller level.
     * If called at the controller level, any views the controller calls also
     * have access to the plugin's code.
     *
     * Each plugin is stored in a separate directory within the directory specified
     * by $config['plugins_path']. An init.php file in the plugin's directory
     * loads the rest of the plugin's code at runtime.
     * @param string,... The list of plugins to be loaded.
     */
    public static function plugins()
    {
      $args = func_get_args();
      if(count($args) == 0) {return false;}
      $klass = self::getInstance();
      $klass->plugins = $args;
    }

    /**
     * Load the requested plugins before the rest of the code executes.
     */
    private function load_plugins()
    {
      if(count($this->plugins) == 0) {return false;}
      self::require_plugins($this->plugins);
    }

    /**
     * Require the requested plugins' init.php files into the program.
     * @param string $array The list of plugin directories.
     */
    public static function require_plugins($array)
    {
      $klass = self::getInstance();
      foreach($array as $plugin) {
        if(array_key_exists('plugins_path', $klass->config)) {
          $file = $klass->config['plugins_path'] . $plugin . '/init.php';
          if(file_exists($file)) {
            require_once($file);
            continue;
          }
        }
          $file = dirname(__FILE__) . '/../plugins/' . $plugin . '/init.php';
          require_once($file);
      }
    }

    /**
     * Set a configuration option.
     * @param string $config The name of the configuration key to set.
     * @param string $value The value for the configuration key.
     */
    public static function set_config($config, $value)
    {
      $klass = self::getInstance();
      $klass->config[$config] = $value;
    }
}

?>