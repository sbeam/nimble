<?php
	require_once('PHPUnit/Framework.php');
	require_once('../nimble.php');
	/**
	* @package FrameworkTest
	*/
	class FileUtilsTest extends PHPUnit_Framework_TestCase {

	  public function setUp() {
		$_SESSION = array();
		$_SESSION['flashes'] = array();
	  }
	
	
	  public function testFileJoinReturnsString() {
		$string = 'test' . DIRECTORY_SEPARATOR . 'myfolder';
		$this->assertEquals($string, FileUtils::join('test', 'myfolder'));
	  }

	}

?>