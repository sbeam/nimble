<?php
require_once(dirname(__FILE__) . '/controller.php');
require_once(dirname(__FILE__) . '/exception.php');
require_once(dirname(__FILE__) . '/helper.php');
require_once(dirname(__FILE__) . '/route.php');

class NiceDog {
    var $routes = array();
    static private $instance = NULL ;
    function __construct()
    {
        if (isset($_GET['url']))
            $this->url =trim($_GET['url'], '/');
        else $this->url = '';
				if(!isset($this->uri)) {
					$this->uri = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
				}
    }
      
    /* Singleton */
    public function getInstance()
      {
        if(self::$instance == NULL)
        {
                self::$instance = new NiceDog();
        }
             return self::$instance;
       }   

    /* Add url to routes */
    public function add_url($rule, $klass, $klass_method, $http_method = 'GET')
    {
						
				/*parse format */
				$has_format = false;
				if(preg_match('/\.[a-zA-Z0-9]+$/', $this->url)) {
					$rule .= '\.(?P<format>[a-zA-Z0-9]+)';
					$has_format = true;
				}		
        $rule = preg_replace('/:([a-zA-Z0-9_]+)(?!:)/', '(?P<\1>[a-zA-Z0-9_-]+)', $rule);
        $this->routes[] = array('/^' . str_replace('/','\/',$rule) . '$/', $klass, $klass_method, $http_method);
    }
    
    /* Process requests and dispatch */
    public function dispatch()
    {	
        foreach($this->routes as $rule=>$conf) {
						/* if a vaild _method is passed in a post set it to the REQUEST_METHOD so that we can route for DELETE and PUT methods */
						if(isset($_POST['_method']) && !empty($_POST['_method']) && in_array(strtoupper($_POST['_method']), Route::$allowed_methods)){
							$_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
						}
						
						/* test to see if its a valid route */
            if (preg_match($conf[0], $this->url, $matches) && $_SERVER['REQUEST_METHOD'] == $conf[3]){
                $matches = $this->parse_urls_args($matches);//Only declared variables in url regex
                $klass = new $conf[1]();

								if($has_format) {
									$klass->http_format = array_pop($matches);
								}else{
									$klass->http_format = 'html';
								}
                ob_start();
                call_user_func_array(array($klass , $conf[2]), $matches);  
                $out = ob_get_contents();
               	ob_end_clean();  
                if (count($klass->headers)>0){
                    foreach($klass->headers as $header){
                        header($header);
                    }
                } 
                print $out;                             
                exit();//Argh! Its not pretty, but usefull...
            }    
        }
				if(empty($_SERVER['REQUEST_METHOD'])){
					throw new NiceDogExecption('No Request Paramater');
				}
        call_user_func(array('r404' , $_SERVER['REQUEST_METHOD']));  
    }   
    
    /* Parse url arguments */
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
}

?>