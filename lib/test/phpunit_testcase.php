<?php

require_once('PHPUnit/Framework.php');

// because we can't guarantee where we'll be in the filesystem, find the
// nearest config/boot.php file from the current working directory.

//set the enviroment to test
$_SERVER['WEB_ENVIRONMENT'] = 'test';

$path_parts = explode(DIRECTORY_SEPARATOR, getcwd());
while (!empty($path_parts)) {
  $path = implode(DIRECTORY_SEPARATOR, array_merge($path_parts, array("config", "boot.php")));
  if (file_exists($path)) {
    define("NIMBLE_IS_TESTING", true);
    define("NIMBLE_RUN", false);
    require_once($path); break;    
  } else {
    array_pop($path_parts);
  } 
}

if (!defined("NIMBLE_IS_TESTING")) {
  throw new Exception("Could not find Nimble config/boot.php from " . getcwd() . "!");
  exit(1); 
}
/** mock session as an array **/
$_SESSION = array();

/**
 * Run PHPUnit tests on Nimble-specific entities.
 */
abstract class NimblePHPUnitTestCase extends PHPUnit_Framework_TestCase {
  const XpathExists = "~!exists!~";
  const XpathNotExists = "~!not exists!~";
  const XpathCount = "~!count!~";
  private $_cached_xml = array();
  private $_redirects = array();
  
  /**
   * Assert that an XPath query matches a node in a particular way.
   * @param string $source The XML source to search.
   * @param $path The XPath to search for.
   * @param $match The type of search to perform.
   * @param $count If $match is self::XpathCount, the number of nodes expected in the result.
   */
  public function assertXpath($source, $path, $match = self::XpathExists, $count = 0) {
    if (($xml = $this->stringToXML($source)) !== false) {
      if (($result = $xml->xpath($path)) !== false) {
        if ($match === true) { $match = self::XpathExists; }
        if ($match === false) { $match = self::XpathNotExists; }
        switch ($match) {
          case self::XpathExists:
            $this->assertTrue(count($result) > 0, "no xpath matches found for <${path}>");
            break;
          case self::XpathNotExists:
            $this->assertTrue(count($result) == 0, "xpath matches found for <${path}>");
            break;
          case self::XpathCount:
            $this->assertEquals($count, count($result), "xpath count of <" . count($result) . "> does not match expected <${count}>");
            break;
          default:
            $this->assertEquals($match, (string)reset($result), "xpath value <" . (string)reset($result) . "> does not match expected <${match}>");
            break;
        }
      } else {
        $this->assertTrue(false, "xpath <${path}> is not valid");
      }
    }
  }
  
  /**
   * Assert the provided text is valid XHTML.
   * @param string $source The text to validate.
   */
  public function assertValidXHTML($source) {
    $this->assertTrue($this->stringToXML($source) !== false, "source is not valid XML");
  }
  
  /**
   * Convert a string to a cached SimpleXMLElement.
   * Print out an error message with the source code if a validation error occurs.
   * @param string $source The text to convert.
   */
  private function stringToXML($source) {
    if (!is_string($source)) { throw new Exception("source must be a string"); }
    $hash = md5($source);
    if (!isset($this->_cached_xml[$hash])) {
      try {
        $xml = new SimpleXMLElement("<x>" . $source . "</x>");
        $this->_cached_xml[$hash] = $xml;
      } catch (Exception $e) {
        $this->_cached_xml[$hash] = false;
        var_dump($e->getMessage());
        
        $lines = explode("\n", $source);
        for ($i = 0, $il = count($lines); $i < $il; ++$i) {
          echo str_pad($i + 1, strlen($il), " ", STR_PAD_LEFT) . ":" . $lines[$i] . "\n"; 
        }
      }
    }
    return $this->_cached_xml[$hash];
  }
  
  /**
   * Render a controller method using the provided template, if necessary.
   * @param Controller $controller The controller to use.
   * @param string $method The method to call on the controller.
   * @param string $template The template to render.
   * @param array $parameters Additional parameters to pass to the controller.
   */
  public function render($controller, $method, $template = "", $parameters = array()) {
    ob_start();
    call_user_func_array(array($controller, $method), $parameters);
    if ($controller->has_rendered === false) {
      if (empty($controller->layout_template) && $controller->layout) {
        $controller->set_layout_template();
      }
      $controller->render($template);
    }
    return ob_get_clean();
  }
}


	abstract class NimblePHPFunctonalTestCase extends PHPUnit_Framework_TestCase {
		/**
			* Loads a controller and mocks a GET HTTP request
			* @param string controller name to call
			* @param string action name to call
			* @param array $action_params array of params to be passed to the controller action that would be passed in by routes 
			* @param array $params array of key => value pairs to be the $_GET or $_POST array
			* @param array $session array with key => value pairs to be the session
			* @return TestRequest
			* @see TestRequest
			* @uses $this->get('TaskController', 'index', array(), array(), array('user_id' => 1));
			*/
		public function get($controller, $action, $action_params = array(), $params = array(), $session = array()) {
			global $_SESSION, $_POST, $_GET;
			$_GET = $params;
			$_SESSION = $session;
			$_POST['METHOD'] = 'GET';
			$obj = new TestRequest();
			$return = $this->load_action($controller, $action, $action_params, $obj);
			return $return;
		}

		/**
			* Loads a controller and mocks a POST HTTP request
			* @param string controller name to call
			* @param string action name to call
			* @param array $action_params array of params to be passed to the controller action that would be passed in by routes 
			* @param array $params array of key => value pairs to be the $_GET or $_POST array
			* @param array $session array with key => value pairs to be the session
			* @return TestRequest
			* @see TestRequest
			* @uses $this->post('TaskController', 'create', array(), array('name' => 'bob'), array('user_id' => 1));
			*/		
		public function post($controller, $action, $action_params = array(), $params = array(), $session = array()) {
			global $_SESSION, $_POST, $_GET;
			$_POST = $_GET = $params;
			$_SESSION = $session;
			$_POST['METHOD'] = 'POST';
			$obj = new TestRequest();
			$return = $this->load_action($controller, $action, $action_params, $obj);
			return $return;
		}

		/**
			* Loads a controller and mocks a PUT HTTP request
			* @param string controller name to call
			* @param string action name to call
			* @param array $action_params array of params to be passed to the controller action that would be passed in by routes 
			* @param array $params array of key => value pairs to be the $_GET or $_POST array
			* @param array $session array with key => value pairs to be the session
			* @return TestRequest
			* @see TestRequest
			* @uses $this->put('TaskController', 'update', array(1), array('name' => 'joe'), array('user_id' => 1));
			*/		
		public function put($controller, $action, $action_params = array(), $params = array(), $session = array()) {
			global $_SESSION, $_POST, $_GET;
			$_POST = $_GET = $params;
			$_SESSION = $session;
			$_POST['METHOD'] = 'PUT';
			$obj = new TestRequest();
			$return = $this->load_action($controller, $action, $action_params, $obj);
			return $return;
		}
		
		/**
			* Loads a controller and mocks a DELETE HTTP request
			* @param string controller name to call
			* @param string action name to call
			* @param array $action_params array of params to be passed to the controller action that would be passed in by routes 
			* @param array $params array of key => value pairs to be the $_GET or $_POST array
			* @param array $session array with key => value pairs to be the session
			* @return TestRequest
			* @see TestRequest
			* @uses $this->delete('TaskController', 'delete', array(1), array(), array('user_id' => 1));
			*/		
		public function delete($controller, $action, $action_params = array(), $params = array(), $session = array()) {
			global $_SESSION, $_POST, $_GET;
			$_POST = $_GET = $params;
			$_SESSION = $session;
			$_POST['METHOD'] = 'DELETE';
			$obj = new TestRequest();
			$return = $this->load_action($controller, $action, $action_params, $obj);
			return $return;
		}
		
		/**
			* @param string $c Controller name you wish to call
			* @param string $action action you wish to call
			* @param array $action_params array of arguments to pass to the action method
			*/
		private function load_action($c, $action, $action_params, $obj) {
			global $_SESSION, $_POST, $_GET;
			$nimble = Nimble::getInstance();
			ob_start();
			$controller = new $c();
			call_user_func_array(array($controller, $action), $action_params);
			$path = strtolower(Inflector::underscore(str_replace('Controller', '', $c)));
			$template = FileUtils::join($path, $action . '.php');
			if ($controller->has_rendered === false) {
	      if (empty($controller->layout_template) && $controller->layout) {
	        $controller->set_layout_template();
	      }
	      $controller->render($template);
	    }
			$obj->response = ob_get_clean();
			$obj->controller = $controller;
			return $obj;
		}
	
	}
	
	class TestRequest {
		var $response;
		var $controller;
	}
	

?>
