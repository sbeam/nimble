<?php
require_once(dirname(__FILE__) . '/route/helper.php');
require_once(dirname(__FILE__) . '/route/url_builder.php');


class Route
{
		static $allowed_methods = array("GET", "POST", "PUT", "DELETE");
    var $pattern;
    var $controller;    
    var $action;
    var $http_method = 'GET';
		var $http_format = '';
    function __construct($pattern){
        $this->pattern = $pattern;
        return $this;
    }
    
    function controller($controller){
        $this->controller = $controller;
        return $this;
    }
    
    function action($action){
        $this->action = $action;
        return $this;
    }
    
    function on($http_method){
        $this->http_method = $http_method;
        $this->bind();
        return $this;
    }
    
    function bind(){
				if(in_array(strtoupper($this->http_method), self::$allowed_methods)){
					$router = Nimble::getInstance()->add_url($this->pattern, $this->controller, $this->action, strtoupper($this->http_method));
				}else{
					throw new NimbleExecption('Invalid Request');
				}  
    }
    /* build the default routes for a controller pass it the prefix ex. Form for FormController */
    public static function resources($controller_prefix) {
			$controller = ucwords($controller_prefix) . 'Controller';
			$controller_prefix = strtolower($controller_prefix);
			$actions = array('index' => 'GET', 'create' => 'POST');
			foreach($actions as $action=>$method) {
				$r = new Route($controller_prefix . 's');
				$r->controller($controller)->action($action)->on($method);
			}
			$actionss = array('update' => 'PUT', 'delete' => 'DELETE', 'show' => 'GET');
			foreach($actionss as $action=>$method) {
				$r = new Route($controller_prefix . '/:id');
				$r->controller($controller)->action($action)->on($method);
			}
		}
}
?>