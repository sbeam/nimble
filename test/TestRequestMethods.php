<?php

require_once('PHPUnit/Framework.php');
require_once('../nimble.php');
	/**
	* @package FrameworkTest
	*/
	class r404 {
		function GET(){}
		function POST(){}
		function DELETE(){}
		function PUT(){}
	}
	/**
	* @package FrameworkTest
	*/
	class TestRequestMethods extends PHPUnit_Framework_TestCase {
		
		public function setUp() {
				$_POST['_method'] = 'GET';
				$_SERVER['REQUEST_METHOD'] = '';
		  $this->Nimble = Nimble::getInstance();
		  $this->Nimble->routes = array();
				$this->Nimble->url = '';
	  }

		public function testDelete() {
			$_POST['_method'] = 'DELETE';
			R('test/:id')->controller('Class')->action('method')->on('DELETE');
			$this->assertEquals($this->Nimble->routes[0][3], $_POST['_method']);
		}
		
		public function testPut() {
			$_POST['_method'] = 'PUT';
			R('test/:id')->controller('Class')->action('method')->on('PUT');
			$this->assertEquals($this->Nimble->routes[0][3], $_POST['_method']);
		}
		/**
		* @expectedException NimbleExecption
		*/
		public function testInvalidMethod() {
			$_POST['_method'] = 'OWNAGE';
			R('test/:id')->controller('Class')->action('method')->on('PUTff');
		}
		/**
		* @expectedException NimbleExecption
		*/	
		public function testInvalidMethodAgain() {
			$_POST['_method'] = 'PUT';
			R('test/:id')->controller('Class')->action('method')->on('PUTff');

		}
		/**
		* @expectedException NimbleExecption
		*/	
		public function testInvalidMethodAgainWithPoo() {
			$_POST['_method'] = 'Poo';
			$this->Nimble->url = 'test/1';
			R('test/:id')->controller('Class')->action('method')->on('PUT');
			$this->Nimble->dispatch();
		}
		
	}

?>
