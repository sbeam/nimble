<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/simple_html_dom.php');
//set the enviroment to test
$_SERVER['WEB_ENVIRONMENT'] = 'test';

// because we can't guarantee where we'll be in the filesystem, find the
// nearest config/boot.php file from the current working directory, unless it's already done.
if (!defined('NIMBLE_RUN')) {
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
}


if (!defined("NIMBLE_RUN")) {
  throw new Exception("Could not find Nimble config/boot.php from " . getcwd() . "!");
  exit(1); 
}
/** mock session as an array **/
$_SESSION = $_POST = $_GET = array();
$_SERVER['REQUEST_METHOD'] = null;

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
            $this->assertTrue(count($result) > 0, "no css selector matches found for <${path}>");
            break;
          case self::XpathNotExists:
            $this->assertTrue(count($result) == 0, "css selector matches found for <${path}>");
            break;
          case self::XpathCount:
            $this->assertEquals($count, count($result), "css selector count of <" . count($result) . "> does not match expected <${count}>");
            break;
          default:
            $this->assertEquals($match, (string)reset($result), "css selector value <" . (string)reset($result) . "> does not match expected <${match}>");
            break;
        }
      } else {
        $this->assertTrue(false, "css selector <${path}> is not valid");
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
        $xml = new SimpleXMLElement("<x>" . $this->clean_xml_string($source) . "</x>");
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
   * Clean a string containing HTML entities for XML parsing for test purposes.
   * @param string $source The string to clean.
   * @return string The cleaned string.
   */
  public function clean_xml_string($source) {
    $source = str_replace(
      array("&nbsp;", "&mdash;", "&ndash;"),
      array(" ", "--", "-"),
      $source
    );
    $source = preg_replace_callback('#&[^\;]+;#', array($this, 'callback_replace_non_xml_entities'), $source);
    return $source;
  }
  
  private function callback_replace_non_xml_entities($matches) {
    return (in_array($matches[0], array("&quot;", "&amp;", "&apos;", "&lt;", "&gt;")) ? $matches[0] : "");
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
      if ($template !== false) {
        if (empty($controller->layout_template) && $controller->layout) {
          $controller->set_layout_template();
        }
        $controller->render($template);
      }
    }
    return ob_get_clean();
  }
}

	/**
	 * Run PHPUnit tests on Nimble-specific entities.
	 * @package testing
	 */
	abstract class NimblePHPFunctionalTestCase extends PHPUnit_Framework_TestCase {
		
		private $controller;
		var $controller_name;
		
		public function __construct() {
			global $_SESSION, $_POST, $_GET, $_SERVER;
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
			$_POST['_method'] = 'GET';
            $_SERVER['REQUEST_METHOD'] = 'GET';
			$this->load_action($action, $action_params);
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
			$_POST['_method'] = 'POST';
            $_SERVER['REQUEST_METHOD'] = 'POST';
			$this->load_action($action, $action_params);
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
			$_POST['_method'] = 'PUT';
            $_SERVER['REQUEST_METHOD'] = 'POST';
			$this->load_action($action, $action_params);
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
			$_POST['_method'] = 'DELETE';
            $_SERVER['REQUEST_METHOD'] = 'POST';
			$this->load_action($action, $action_params);
		}
		
		
		
		/**
			* Assert that the correct url was redirected to
			* @param string $url url you want to assert the controller redirected to
			*/
		
		public function assertRedirect($url) {
			$test = "Location: {$url}";
			$this->header_test($test);
		}
		
		
		public function headers() {
			return $this->controller->headers;
		}
		
		
		/**
			* Assert that the correct content type header is set
			* @param string $type content type you wish to test for (must be a vaild content type ex. text/html, text/xml)
			*/
		
		public function assertContentType($type) {
			$test = "Content-Type: {$type}";
			$this->header_test($test);
		}
		
		
		private function header_test($test) {
			$headers = $this->headers();
			foreach($headers as $header) {
				if(strtolower($header[0]) == strtolower($test)) {
					$this->assertTrue(true);
					return;
				}
			}
			$this->assertTrue(false, "No header found matching " . $test);
		}
		
		
		private function check_response_code($start, $end=null) {
			$headers = $this->headers();
			if($start == $end) {
				$message = "No response code found for " . $start;
			}else{
				$message = "No response code found between " . $start . " and " . $end;
			}
			foreach($headers as $header) {
				$code = $header[1];
				if($code <= $end && $code >= $start) {
					$this->assertTrue(true);
					return;
				}
			}
			$this->assertTrue(false, $message);
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
			* Asserts that a node exists matching the css selector expression
			* @param string $selector expression
			*/
		public function assertSelector($selector) {
			$html = str_get_html($this->response);
			$values = $html->find($selector);
			$assert = (count($values) > 0);
			$this->assertTrue($assert, "No css selector node found for " . $selector);
		}
		
		/**
			* Asserts that a {n} node(s) exists matching the css selector expression
			* @param integer $number_of_nodes the number of nodes you expect to be returned
			* @param string $selector expression
			*/
		public function assertSelectorNodes($selector, $number_of_nodes) {
			$html = str_get_html($this->response);
			$values = $html->find($selector);
			$this->assertEquals($number_of_nodes, count($values));
		}
		/**
			* Asserts that a node exists matching the css selector expression
			* @param string $value the value you want to match within the css selector node
			* @param string $selector expression
			*/
		public function assertSelectorValue($selector, $value) {
		  if (!is_array($value)) { $value = array($value); }
			$html = str_get_html($this->response);
			$values = $html->find($selector);
			if (count($values) == count($value)) {
			  for ($i = 0, $il = count($values); $i < $il; ++$i) {
    			$node_value = $values[$i]->innertext;
    			$this->assertEquals($node_value, $value[$i], sprintf("Node value <%s> does not match expected <%s> at index %d", $node_value, $values[$i], $i));	    
			  }
			} else {
			  $this->assertTrue(false, sprintf("Count of nodes found <%d> don't match count of values expected <%d>", count($values), count($value)));
			}
		}
		
		/**
		 * Asserts that a node exists matching the Xpath expression
		 * @param string $xpath the xpath to match
		 */
        public function assertXpath($xpath) {
            try {
                $xml = new SimpleXMLElement($this->fix_string_for_xml($this->response));
                $nodes = $xml->xpath($xpath);
                if ($nodes === false) {
                    $this->assertTrue(false, "Xpath is not valid: " . $xpath);  		    
                } else {
                    $this->assertTrue(count($nodes) > 0, "No Xpath nodes found for " . $value);
                }
            } catch (Exception $e) {
                $this->assertTrue(false, "Response is not valid XML");
            }
        }
		
		/**
		 * Asserts that a {n} node(s) exists matching the Xpath expression
		 * @param string $xpath the xpath to match
		 * @param string $number_of_nodes the xpath to match
		 */
		public function assertXpathNodes($xpath, $number_of_nodes) {
		  try {
  		  $xml = new SimpleXMLElement($this->fix_string_for_xml($this->response));
  		  $nodes = $xml->xpath($xpath);
  		  if ($nodes === false) {
		      $this->assertTrue(false, "Xpath is not valid: " . $xpath);
  		  } else {
    		  $this->assertEquals($number_of_nodes, count($nodes), "No Xpath nodes found for " . $xpath);  		     
  		  }
		  } catch (Exception $e) {
		    $this->assertTrue(false, "Response is not valid XML");
		  }
		}

		/**
		 * Asserts that the value of the node founds via Xpath matches the requested value
		 * @param string $xpath the xpath to match
		 * @param string|array $value the value or values to match
		 */
        public function assertXpathValue($xpath, $value, $is_regexp=false) {
            if (!is_array($value)) { $value = array($value); }

            try {
                $xml = new SimpleXMLElement($this->fix_string_for_xml($this->response));
            } catch (Exception $e) {
                $this->assertTrue(false, "Response is not valid XML");
            }
            $nodes = $xml->xpath($xpath);

            if ($nodes === false) {
                $this->assertTrue(false, "Xpath is not valid: " . $xpath);
            } else {
                if (count($nodes) === count($value)) {
                    for ($i = 0, $il = count($nodes); $i < $il; ++$i) {
                        $txt = preg_replace('#^<(\w[\w\d]*)\b[^>]*>(.*?)</\1>#i', '\\2', (string)$nodes[$i]->asXML());
                        if ($is_regexp)
                            $this->assertRegExp($value[$i], $txt, sprintf("Node value <%s> does not match pattern <%s> at index %d", $txt, $value[$i], $i));
                        else
                            $this->assertEquals($value[$i], $txt, sprintf("Node value <%s> does not match expected <%s> at index %d", $txt, $value[$i], $i));
                    }
                } else {
                    $this->assertTrue(false, sprintf("Count of nodes found <%d> doesn't match count of values expected <%d>", count($nodes), count($value)));
                }
            }
        }



        /**
         * asset that $this->response equal to the given string
         *
         * @param string $str       comparison
         * @param bool $strip       remove newlines before compare? (default true)
         */
        function assertResponseText($str, $strip=true) {
            $txt = ($strip)? preg_replace('/[\r\n]+/', '', $this->response) : $this->response;
            $this->assertEquals($str, $txt);
        }


        /**
         * assert that mail headers contain all the given patterns 
         *
         * @param string $headers       Subject: xxxx \n Content-type: text/plain, etc.
         * @param array $matches        Subject => REGEXP, Content-type: REGEXP, etc.
         */
        function assertMailHasHeaders($headers, $matches) {
            $hdrs = array();
            foreach (split("\n", $headers) as $hdr) {
                list($k, $v) = split(':', $hdr);
                $hdrs[$k] = trim($v);
            }
            foreach ($matches as $k => $pat) {
                $this->assertTrue( !empty($hdrs[$k]) );
                if ($k == 'Subject') {
                    if (preg_match('/^\s*=\?UTF-8\?B\?([\w\d]+=*)\?=$/', $hdrs[$k], $m)) { // crazy UTF header match.
                        $hdrs[$k] = base64_decode($m[1]);
                    }
                }
                $this->assertRegExp($pat, $hdrs[$k]);
            }
        }


		/**
			* Returns a controller variable
			* @param string $var the name of the controller variable
			*/
		public function assigns($var) {
			return $this->controller->$var;
		}
		
		/**
			* Helper function for adding .php to a string
			* @param string $name string to add .php
			*/		
		private function add_php_extension($name) {
			if(strpos($name, '.php') === false) {
				$name = $name . ".php";
			}
			return $name;
		}
		
		/**
			* Asserts that the given template has been rendered
			* @param string $name the name of the template with or without .php extension
			*/
		public function assertTemplate($name) {
			$name = basename($name);
			$name = $this->add_php_extension($name);
			$template_rendered = basename($this->controller->template);
			$this->assertEquals($name, $template_rendered);
		}
		/**
			* Asserts that the given partial template has been rendered
			* @param string $name the name of the partial template with or without .php extension
			*/
		public function assertPartial($name) {
			$name = basename($name);
			$name = $this->add_php_extension($name);
			$partials = $this->controller->rendered_partials;
			$base = array();
			foreach($partials as $partial) {
				$base[] = basename($partial);
			}
			$base = array_flip($partial);
			$this->assertTrue(isset($base[$name]), "No partial matching $name was found");
		}
		
		public function assertResponse($code) {
			$shortcuts = array('success' => 200, 'redirect' => array(300,399), 'missing' => 404, 'error' => array(500, 599));
			$message = "Expected response to be a ?, but was ?";
			if(is_string($code) && isset($shortcuts[$code])) {
				$code = $shortcuts[$code];
			}
			if(is_array($code)) {
				$start = reset($code);
				$end = end($code);
			}else{
				$start = $code;
				$end = $code;
			}
			$this->check_response_code($start, $end);
		}
		
		/**
			* @param string $action action you wish to call
			* @param array $action_params array of arguments to pass to the action method
			*/
        private function load_action($action, $action_params) {
            global $_SESSION, $_POST, $_GET;
            $nimble = Nimble::getInstance();
            ob_start();

            $controller = new $this->controller_name();

            $controller->format = (!empty($action_params['format'])) ? $action_params['format'] : $controller->default_format;

            call_user_func(array($controller, "run_before_filters"), $action);

            call_user_func_array(array($controller, $action), array($action_params));

            $path = strtolower(Inflector::underscore(str_replace('Controller', '', $this->controller_name)));
            $template = FileUtils::join($path, $action);

            if ($controller->has_rendered === false) {
                if (empty($controller->layout_template) && $controller->layout) {
                    $controller->set_layout_template();
                }
                $controller->render($template);
            }

            call_user_func(array($controller, "run_after_filters"), $action);

            $this->response = ob_get_clean();
            $this->controller = $controller;
        }

    /**
     * Strip out non-XML entities from a string for XML parsing.
     * @param string the string to process
     * @return string the repaired string
     */ 
        private function fix_string_for_xml($string) {
            if (is_string($string)) {
                $string = str_replace('xmlns=', 'ns=', $string); // simpleXML wont work with simple xpaths unless this is done.
                return preg_replace_callback('#&[^\;]+;#', array($this, 'fix_xml_tags_callback'), $string);
            }
            return $string;
        }
    
    /**
     * Callback for fix_string_for_xml
     */
    private function fix_xml_tags_callback($matches) {
      if (!in_array($matches[0], array('&quot;', '&amp;', '&apos;', '&lt;', '&gt;'))) {
        return "";  
      }
      return $matches[0];
    }
	
	}
	

?>
