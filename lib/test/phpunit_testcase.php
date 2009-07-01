<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/simple_html_dom.php');
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
$_SESSION = $_POST = $_GET = array();

/**
 * Run PHPUnit tests on Nimble-specific entities.
 * @package testing
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
    $this->assertTrue(self::stringToXML($source) !== false, "source is not valid XML");
  }
  
  /**
   * Convert a string to a cached SimpleXMLElement.
   * Print out an error message with the source code if a validation error occurs.
   * @param string $source The text to convert.
   */
  public function stringToXML($source) {
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

	/**
	 * Run PHPUnit tests on Nimble-specific entities.
	 * @package testing
	 */
	abstract class NimblePHPFunctonalTestCase extends PHPUnit_Framework_TestCase {
		
		private $controller;
		var $controller_name;
		
		public function __construct() {
			global $_SESSION, $_POST, $_GET;
			$_SESSION = $_POST = $_GET = array();
			parent::__construct();
			$class = get_class($this);
			$this->controller_name = str_replace('Test', '', $class);
			$this->controller = '';
		}
		
		
		/**
			* Loads a controller and mocks a GET HTTP request
			* @param string action name to call
			* @param array $action_params array of params to be passed to the controller action that would be passed in by routes 
			* @param array $params array of key => value pairs to be the $_GET or $_POST array
			* @param array $session array with key => value pairs to be the session
			* @uses $this->get('TaskController', 'index', array(), array(), array('user_id' => 1));
			*/
		public function get($action, $action_params = array(), $params = array(), $session = array()) {
			global $_SESSION, $_POST, $_GET;
			$_GET = $params;
			$_SESSION = $session;
			$_POST['METHOD'] = 'GET';
			$this->load_action($action, $action_params, $obj);
		}

		/**
			* Loads a controller and mocks a POST HTTP request
			* @param string action name to call
			* @param array $action_params array of params to be passed to the controller action that would be passed in by routes 
			* @param array $params array of key => value pairs to be the $_GET or $_POST array
			* @param array $session array with key => value pairs to be the session
			* @uses $this->post('TaskController', 'create', array(), array('name' => 'bob'), array('user_id' => 1));
			*/		
		public function post($action, $action_params = array(), $params = array(), $session = array()) {
			global $_SESSION, $_POST, $_GET;
			$_POST = $_GET = $params;
			$_SESSION = $session;
			$_POST['METHOD'] = 'POST';
			$this->load_action($action, $action_params, $obj);
		}

		/**
			* Loads a controller and mocks a PUT HTTP request
			* @param string action name to call
			* @param array $action_params array of params to be passed to the controller action that would be passed in by routes 
			* @param array $params array of key => value pairs to be the $_GET or $_POST array
			* @param array $session array with key => value pairs to be the session
			* @uses $this->put('TaskController', 'update', array(1), array('name' => 'joe'), array('user_id' => 1));
			*/		
		public function put($action, $action_params = array(), $params = array(), $session = array()) {
			global $_SESSION, $_POST, $_GET;
			$_POST = $_GET = $params;
			$_SESSION = $session;
			$_POST['METHOD'] = 'PUT';
			$this->load_action($action, $action_params, $obj);
		}
		
		/**
			* Loads a controller and mocks a DELETE HTTP request
			* @param string action name to call
			* @param array $action_params array of params to be passed to the controller action that would be passed in by routes 
			* @param array $params array of key => value pairs to be the $_GET or $_POST array
			* @param array $session array with key => value pairs to be the session
			* @uses $this->delete('TaskController', 'delete', array(1), array(), array('user_id' => 1));
			*/		
		public function delete($action, $action_params = array(), $params = array(), $session = array()) {
			global $_SESSION, $_POST, $_GET;
			$_POST = $_GET = $params;
			$_SESSION = $session;
			$_POST['METHOD'] = 'DELETE';
			$this->load_action($controller, $action, $action_params, $obj);
		}
		
		
		
		/**
			* Assert that the correct url was redirected to
			* @param string $url url you want to assert the controller redirected to
			*/
		
		public function assertRedirect($url) {
			$hash = array_flip($this->controller->headers);
			$this->assertTrue(isset($hash["Location: {$url}"]));
		}
		
		
		/**
			* Assert that the correct content type header is set
			* @param string $type content type you wish to test for (must be a vaild content type ex. text/html, text/xml)
			*/
		
		public function assertContentType($type) {
			$hash = array_flip($this->controller->headers);
			$this->assertTrue(isset($hash["Content-Type: {$type}"]));
		}
		
		/**
			* Looks for a string match in the response text
			* @param string $value Item you wish to look for in the response text
			*/
		public function responseIncludes($value) {
			if(strpos($this->response, $value) === false) {
				$this->assertTrue(false, $value . " is not in the response");
			}else{
				$this->assertTrue(true);
			}
		}
		
		/**
			* Asserts that a node exists matching the xpath expression
			* @param string $xpath expression
			*/
		public function assertXpath($xpath) {
			$html = str_get_html($this->response);
			$values = $html->find($xpath);
			$assert = (count($values) > 0);
			$this->assertTrue($assert, "No Xpath node found for " . $xpath);
		}
		
		/**
			* Asserts that a {n} node(s) exists matching the xpath expression
			* @param integer $number_of_nodes the number of nodes you expect to be returned
			* @param string $xpath expression
			*/
		public function assertXpathNodes($xpath, $number_of_nodes) {
			$html = str_get_html($this->response);
			$values = $html->find($xpath);
			$this->assertEquals($number_of_nodes, count($values));
		}
		/**
			* Asserts that a node exists matching the xpath expression
			* @param string $value the value you want to match within the xpath node
			* @param string $xpath expression
			*/
		public function assertXpathValue($xpath, $value) {
			$html = str_get_html($this->response);
			$values = $html->find($xpath);
			$text = $values[0]->innertext;
			$this->assertEquals($text, $value);
		}
		
		/**
			* Returns a controller variable
			* @param string $var the name of the controller variable
			*/
		public function assigns($var) {
			return $this->controller->$var;
		}
		
		
		/**
			* Asserts that the given template has been rendered
			* @param string $name the name of the template with or without .php extension
			*/
		public function assertTemplate($name) {
			$name = basename($name);
			if(strpos($name, '.php') === false) {
				$name = $name . ".php";
			}
			$template_rendered = basename($this->controller->template);
			$this->assertEquals($name, $template_rendered);
		}
		
		/**
			* @param string $c Controller name you wish to call
			* @param string $action action you wish to call
			* @param array $action_params array of arguments to pass to the action method
			*/
		private function load_action($action, $action_params, $obj) {
			global $_SESSION, $_POST, $_GET;
			$nimble = Nimble::getInstance();
			ob_start();
			$controller = new $this->controller_name();
			call_user_func_array(array($controller, $action), $action_params);
			$path = strtolower(Inflector::underscore(str_replace('Controller', '', $this->controller_name)));
			$template = FileUtils::join($path, $action . '.php');
			if ($controller->has_rendered === false) {
	      if (empty($controller->layout_template) && $controller->layout) {
	        $controller->set_layout_template();
	      }
	      $controller->render($template);
	    }
			$this->response = ob_get_clean();
			$this->controller = $controller;
		}
	
	}
	

?>
