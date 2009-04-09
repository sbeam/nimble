<?php
require_once('PHPUnit/Framework.php');
require_once('../nimble.php');
	/**
	* @package FrameworkTest
	*/
	class TestRender extends PHPUnit_Framework_TestCase {
		public function setUp() {
			$_SERVER['REQUEST_METHOD'] = 'GET';
			$this->Nimble = Nimble::getInstance();
			$this->Nimble->routes = array();
					$this->url = '';
			Nimble::set_config('plugins_path', join(DIRECTORY_SEPARATOR, array(dirname(__FILE__) , 'test_plugins')));
			Nimble::set_config('view_path', join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'views')));
		}
		
		
		public function testAutoRender() {
			$this->Nimble->url = "";
			$this->Nimble->add_url('', "MyTestController", "test");	
			$this->Nimble->dispatch(true);
			$this->assertTrue($this->Nimble->klass->has_rendered);
		}
		
		public function testManualRender() {
			$this->Nimble->url = "";
			$this->Nimble->add_url('', "MyTestController", "test2");	
			$this->Nimble->dispatch(true);
			$this->assertTrue($this->Nimble->klass->has_rendered);
		
		}
		
		/**
		* @expectedException NimbleExecption
		*/
		public function testDoubleRenderThrowsExecption() {
			$this->Nimble->url = "";
			$this->Nimble->add_url('', "MyTestController", "test3");	
			$this->Nimble->dispatch(true);
		}
		

	}

	/**
	* @package FrameworkTest
	*/
	class MyTestController extends Controller {
		
		public function __construct() {
			$this->layout = false;
		}
		
		public function test() {
			
		}
		
		public function test3() {
			$this->render(join(DIRECTORY_SEPARATOR, array('my_test', 'test.php')));
			$this->render(join(DIRECTORY_SEPARATOR, array('my_test', 'test.php')));
		}
		
		public function test2() {
			$this->render(join(DIRECTORY_SEPARATOR, array('my_test', 'test.php')));
		}
	}


?>