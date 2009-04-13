<?php

require_once('PHPUnit/Framework.php');
require_once('../nimble.php');
	/**
	* @package FrameworkTest
	*/
	class TestRoutes extends PHPUnit_Framework_TestCase {
		public function setUp() {
			$_SESSION = array();
			$_SESSION['flashes'] = array();
			$this->Nimble = Nimble::getInstance();
			$this->Nimble->test_mode = true;
			$this->Nimble->routes = array();
			$this->url = '';
		}

		/**
		 * @dataProvider providerRubyOnRailsRoutes
		 */
		public function testRubyOnRailsRoutes($ror_route, $expected_pattern) {
			$_SESSION = array();
			$_SESSION['flashes'] = array();
			$this->Nimble->add_url($ror_route, "Class", "method");
			$this->assertEquals("/^" . str_replace('/', '\/', $expected_pattern) . "$/", $this->Nimble->routes[0][0]);
					
		}

		public function providerRubyOnRailsRoutes() {
			$pattern = "[a-zA-Z0-9_-]+";

			return array(
				array(":id", "(?P<id>{$pattern})"),
				array("view/:id", "view/(?P<id>{$pattern})"),
				array("view/:id1", "view/(?P<id1>{$pattern})"),
				array("view/:1id", "view/(?P<1id>{$pattern})"),
				array("view/:i_d", "view/(?P<i_d>{$pattern})"),
				array("view/:i-d", "view/(?P<i>{$pattern})-d"),
				array("view/:id/action", "view/(?P<id>{$pattern})/action"),
				array("view/:id/action/:id2", "view/(?P<id>{$pattern})/action/(?P<id2>{$pattern})"),
				array(":id:id2", "(?P<i>{$pattern})d(?P<id2>{$pattern})")
			);
		}

		public function testFormatRoutes() {
			$this->Nimble->url = "test";
			$this->Nimble->add_url('', "Class", "method");
			$this->assertEquals("/^$/", $this->Nimble->routes[0][0]);

			$this->Nimble->routes = array();
			$this->Nimble->url = "test.xml";
			$this->Nimble->add_url('', "Class", "method");
			$this->assertEquals("/^\.(?P<format>[a-zA-Z0-9]+)$/", $this->Nimble->routes[0][0]);
		}

			public function testResources() {
				Route::resources('Form');
				$this->assertEquals(count($this->Nimble->routes), 6);
			}
			
			public function testUrlFor() {
				$this->Nimble->routes = array();
				$this->Nimble->uri = '';
				$this->Nimble->url = '/class/1';
				$this->Nimble->add_url('/class/:method', "Class", "Method");
				$this->assertEquals('/class/1', UrlBuilder::url_for('Class', 'Method', 1));
			}
			
			/**
			* @expectedException NimbleExecption
			*/
			public function testUrlForFailsWrongParams() {
				$this->Nimble->routes = array();
				$this->Nimble->uri = '';
				$this->Nimble->url = '/class/1';
				$this->Nimble->add_url('/class/:method', "Class", "Method");
				$this->assertEquals('/class/1', UrlBuilder::url_for('Class', 'Method', 1, 2));
			}
			
			
			
			


	}

?>
