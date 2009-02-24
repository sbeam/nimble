<?php

require_once('PHPUnit/Framework.php');
require_once('../NiceDog.php');

class TestRequests extends PHPUnit_Framework_TestCase {
	
	public function setUp() {
			$_POST['_method'] = '';
      $this->nicedog = NiceDog::getInstance();
      $this->nicedog->routes = array();
  }

	public function testDelete() {
		$_POST['_method'] = 'DELETE';
 		R('test/:id')->controller('Class')->action('method')->on('DELETE');
		$this->assertEquals($this->nicedog->routes[0][3], $_POST['_method']);
	}
	
	public function testPut() {
		$_POST['_method'] = 'PUT';
 		R('test/:id')->controller('Class')->action('method')->on('PUT');
		$this->assertEquals($this->nicedog->routes[0][3], $_POST['_method']);
	}

	public function testInvalidMethod() {
		$_POST['_method'] = 'OWNAGE';
 		R('test/:id')->controller('Class')->action('method')->on('PUT');
		$this->assertNotEquals($this->nicedog->routes[0][3], $_POST['_method']);
	}
	
}

?>