<?php
	class UrlBuilder {
		
		
		public static function getInstance() {
      if(self::$instance == NULL) {
      	self::$instance = new self();
      }
    	return self::$instance;
    }
		/*
			Cleans all the regex cruft out of the URL expression
			@params string $route
		*/
		public static function clean_route($route) {
			return str_replace('$/', '', str_replace('/^', '', str_replace('\/','/', $route)));
		}

		/* This method does all the heavy lifting for figuring out how to format the route back into something useable by a browser */
		public static function build_url($route, $params=array()) {
			$route_regex = '/\(\?P<[\w]+>[^\)]+\)/'; // matches (?P<foo>[a-zA-Z0-9]+) etc.
			$pattern = $route[0]; // in not typing $route[0] over and over making it a local var
			$pattern = self::clean_route($pattern);
			if(!empty($params) && preg_match_all($route_regex, $pattern, $matches)){
				//test if we have the right number of params
				if (count($matches[0]) != count($params)) {
					throw new NiceDogExecption('Invalid Number of Params expected: ' . count($matches[0]) . ' Given: ' . count($params));
				}
				//replace the regular expression syntax with the params
				return preg_replace(array_fill(0, count($params), $route_regex), $params, $pattern, 1); 
			}else{
				return $pattern;
			}
		}
		
		/*
		 	@params string $controller, string $action, array $params
		*/
		public static function url_for($controller, $action, $params=array()){
			$klass = NiceDog::getInstance();
			foreach($klass->routes as $route) {
				if(strtolower($route[1]) == strtolower($controller) && strtolower($route[2]) == strtolower($action)) {
					return self::build_url($route, $params);
				}
			}
			throw new NiceDogException('Invalid Controller / Method Pair');
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