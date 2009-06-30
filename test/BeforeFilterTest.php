<?php
require_once('PHPUnit/Framework.php');
require_once('../nimble.php');
/**
* @package FrameworkTest
*/
class TestPluginLoader extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$_SESSION = array();
		$_SESSION['flashes'] = array();
		$this->controller = new BeforeFilterTestController();
	}
	
	
	public function testGlobalBeforeFilter() {
		$c = $this->controller;
		$c->run_before_filters('index');
		
		$this->assertTrue($c->global);
		$this->assertTrue($c->for_index);
		$this->assertFalse($c->for_index2);
		$this->assertFalse($c->except_index);
	}
	
	
}
	
	class BeforeFilterTestController extends Controller {
		var $global = false;
		var $for_index = false;
		var $for_index2 = false;
		var $except_index = false;
		
		
		public function before_filter() {
			$this->global = true;
		}
		
		public function before_filter_for_index() {
			$this->for_index = true;
		}
		
		public function before_filter_for_index2() {
			$this->for_index2 = true;
		}
		
		public function before_filter_except_index() {
			$this->except_index = true;	
		}
		
		public function index() {
			return true;
		}
		
	}
	
?>