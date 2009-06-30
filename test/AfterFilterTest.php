<?php
require_once('PHPUnit/Framework.php');
require_once('../nimble.php');
/**
* @package FrameworkTest
*/
class AfterFilterTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$_SESSION = array();
		$_SESSION['flashes'] = array();
		$this->controller = new AfterFilterTestController();
	}
	
	
	public function testGlobalAfterFilter() {
		$c = $this->controller;
		$c->run_after_filters('index');
		$this->assertTrue($c->global);
	}
	
	public function testIndexOnly() {
		$c = $this->controller;
		$c->run_after_filters('index');
		$this->assertTrue($c->for_index);
		$this->assertFalse($c->for_index2);
		$this->assertFalse($c->except_index);
	}
	
	public function testIndex2Only() {
		$c = $this->controller;
		$c->run_after_filters('index2');
		$this->assertTrue($c->for_index2);
		$this->assertFalse($c->for_index);
		$this->assertTrue($c->except_index);
	}
	
	
}
	
	class AfterFilterTestController extends Controller {
		var $global = false;
		var $for_index = false;
		var $for_index2 = false;
		var $except_index = false;
		
		
		public function after_filter() {
			$this->global = true;
		}
		
		public function after_filter_for_index() {
			$this->for_index = true;
		}
		
		public function after_filter_for_index2() {
			$this->for_index2 = true;
		}
		
		public function after_filter_except_index() {
			$this->except_index = true;	
		}
		
		public function index() {
			return true;
		}
		
		public function index2() {
			return true;
		}
		
	}
	
?>