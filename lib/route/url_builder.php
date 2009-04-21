<?php
$dir = dirname(__FILE__);
require_once($dir . '/../exception.php');
  /**
  * UrlBuilder constructs a usable URL from a Route pattern.
	* @package Route
  */
  class UrlBuilder {
      /**
       * Get the current UrlBuilder object.
       * @return UrlBuilder The current UrlBuilder.
       */
      public static function getInstance()
      {
          if(self::$instance == NULL) {
              self::$instance = new self();
          }
          return self::$instance;
      }

      /**
       * Cleans up the route pattern.
       * @param string $route The route pattern.
       * @return string The cleaned up route pattern. 
       */
      public static function clean_route($route)
      {
          return str_replace('$/', '', str_replace('/^', '', str_replace('\/','/', $route)));
      }

      /**
       * Get the current request's URI.
       * @return string The current request's URI.
       */
      public static function uri() {
          $klass = Nimble::getInstance();
          return $klass->uri;
      }

      /**
       * Build a URL based on a route and a list of parameters that match the route's pattern's named parameters.
       * @param Route $route The route.to match.
       * @param array $params The array of params in the order they occur in the url.
       * @return string The constructed URL, or the original pattern if no match.
       * @throws NimbleException if the wrong number of parameters for the included pattern are provided.
       */
      public static function build_url($route, $params=array()) {
          $route_regex = '/\(\?P<[\w]+>[^\)]+\)/'; // matches (?P<foo>[a-zA-Z0-9]+)
          $pattern = self::clean_route($route[0]);
          if(!empty($params) && preg_match_all($route_regex, $pattern, $matches)){
              // test if we have the right number of params
              if (count($matches[0]) != count($params)) {
                  throw new Execption('Invalid Number of Params expected: ' . count($matches[0]) . ' Given: ' . count($params));
              }

              // replace the regular expression syntax with the params
              return str_replace('//', '/', self::uri() . preg_replace(array_fill(0, count($params), $route_regex), $params, $pattern, 1));
          }else{
              return $pattern;
          }
      }

      /**
       * Build a URL that points at the provided controller and action, with the provided params to match.
       * @param string $controller, $action, $params ...
	   * usage: url_for('controller', 'action')
       * @return string The constructed URL.
       * @throws NimbleException if neither the controller nor the action match.
       */
      public static function url_for()
      {	
			
			$args = func_get_args();
			$controller = array_shift($args);
			$action = array_shift($args);
			$params = $args;
	  
          $klass = Nimble::getInstance();
          foreach($klass->routes as $route) {
				var_dump($route);
              if(strtolower($route[1]) == strtolower($controller) && strtolower($route[2]) == strtolower($action)) {
                  return self::build_url($route, $params);
              }
          }
          throw new Exception('Invalid Controller / Method Pair');
      }

      /**
       * Dump out application routes to a human readable format.
       * @param boolean $cli True if being called from the command line.
       * @return string The application's routes in a human readable format.
       */
      public static function dumpRoutes($cli=false)
      {
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
  // Global functions

  /**
   * Build a URL that points at the provided controller and action, with the provided params to match.
   * @uses url_for('controller', 'action', 'param1', 'params2', 'etc')
   * @return string The constructed URL.
   * @throws NimbleException if neither the controller nor the action match.
   */
  function url_for() {
      return UrlBuilder::url_for(join(',', func_get_args()));
  }
?>