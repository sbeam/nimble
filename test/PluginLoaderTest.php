<?php

	require_once('PHPUnit/Framework.php');
	require_once('../nimble.php');
	/**
	* @package FrameworkTest
	*/
	class PluginLoaderTest extends PHPUnit_Framework_TestCase {
		public function setUp() {
			$_SESSION = array();
			$_SESSION['flashes'] = array();
			$this->Nimble = Nimble::getInstance();
			$this->Nimble->routes = array();
			$this->url = '';
			Nimble::set_config('plugins_path', dirname(__FILE__) . '/test_plugins/');
		}


			public function testSettingConfig() {
				$this->assertEquals(dirname(__FILE__) . '/test_plugins/', $this->Nimble->config['plugins_path']);
			}
			
			public function testPluginGetsLoaded() {
				Nimble::plugins('test_plugin');
				$this->Nimble->__construct();
				$this->Nimble->dispatch(true);
				$test_class = new TestPlugin();
				$this->assertEquals($test_class->foo(), 'foo');
			}

			public function testCanLoadNimblePLuginFormHelper() {
				Nimble::plugins('remote_form_helper');
				$this->Nimble->__construct();
				$this->Nimble->dispatch(true);
				new RemoteFormHelper();
				$this->assertEquals(1,1);
			}

			public function testLoadBothCustomAndNimble() {
				Nimble::plugins('remote_form_helper', 'test_plugin');
				$this->Nimble->__construct();
				$this->Nimble->dispatch(true);
				$test_class = new TestPlugin();
				$this->assertEquals($test_class->foo(), 'foo');
				new RemoteFormHelper();
				$this->assertEquals(1,1);
			}
			
			public function testLoadPluginAtController() {
				/* test controller is below */
				$klass = new TestController($this);
				$test_class = new TestPlugin();
				$this->assertEquals($test_class->foo(), 'foo');
			}
	}
	
	/**
	* @package FrameworkTest
	* Test controller 
	*/
	class TestController extends Controller {
		public function __construct($test) {
			$this->load_plugins('test_plugin');
			$test_class = new TestPlugin();
			$test->assertEquals($test_class->foo(), 'foo');
		}
	}

?>