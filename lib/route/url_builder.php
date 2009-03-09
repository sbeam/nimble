<?php


	/*
		@package UrlBuilder
	*/

	class UrlBuilder {
		
		
		public static function getInstance() {
      if(self::$instance == NULL) {
      	self::$instance = new self();
      }
    	return self::$instance;
    }
		/*
			Cleans all the regex cruft out of the URL expression
			@param string $route
		*/
		public static function clean_route($route) {
			return str_replace('$/', '', str_replace('/^', '', str_replace('\/','/', $route)));
		}
		
		public static function uri() {
			$klass = Nimble::getINstance();
			return $klass->uri;
		}

		/* 
			This method does all the heavy lifting for figuring out how to format the route back into something useable by a browser 
			@param string $route the route path
			@param string $params array of params in the order they occure in the url
		*/
		public static function build_url($route, $params=array()) {
			$route_regex = '/\(\?P<[\w]+>[^\)]+\)/'; // matches (?P<foo>[a-zA-Z0-9]+) etc.
			$pattern = $route[0]; // in not typing $route[0] over and over making it a local var
			$pattern = self::clean_route($pattern);
			if(!empty($params) && preg_match_all($route_regex, $pattern, $matches)){
				//test if we have the right number of params
				if (count($matches[0]) != count($params)) {
					throw new NimbleExecption('Invalid Number of Params expected: ' . count($matches[0]) . ' Given: ' . count($params));
				}
				//replace the regular expression syntax with the params
				return str_replace('//', '/', self::uri() . preg_replace(array_fill(0, count($params), $route_regex), $params, $pattern, 1)); 
			}else{
				return $pattern;
			}
		}
		
		/*
		 	@param string $controller, string $action, array $params
		*/
		public static function url_for($controller, $action, $params=array()){
			$klass = Nimble::getInstance();
			foreach($klass->routes as $route) {
				if(strtolower($route[1]) == strtolower($controller) && strtolower($route[2]) == strtolower($action)) {
					return self::build_url($route, $params);
				}
			}
			throw new NimbleException('Invalid Controller / Method Pair');
		}
		/*
			dumps the routes to a human readable format
			@param boolean $cli
		*/
		public static function dumpRoutes($cli=false) {
			$klass = Nimble::getInstance();
			$out = array();
			foreach($klass->routes as $route) {
				$pattern = self::clean_route($route[0]);
				$pattern = empty($pattern) ? 'root path' : $pattern;
				array_push($out, "Controller: {$route[1]} Action: {$route[2]} Method: {$route[3]} Pattern: " . $pattern);
			}
			$return = "\n";
			$return .= join("\n", $out);
			$return .= "\n";
			return $cli ? $return : htmlspecialchars($return);
		}
		
		
	}
	
	
	/* Global functions */
	
	/* 
		@params string $controller, string $action, array $params
	*/
	function url_for($controller, $action, $params=array()) {
		return UrlBuilder::url_for($controller, $action, $params);
	}
	

?>