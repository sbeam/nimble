<?php

require_once('PHPUnit/Framework.php');
require_once('../nimble.php');

class TestPluginLoader extends PHPUnit_Framework_TestCase {
    public function setUp() {
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
			$test_class = new TestPlugin();
			$this->assertEquals($test_class->foo(), 'foo');
		}
		
		public function testCanLoadNimblePLuginFormHelper() {
			Nimble::plugins('form_helper');
			$this->Nimble->__construct();
			try{
				new FormHelper();
				$this->assertEquals(1,1);
			} catch(Exception $e) {
				echo $e->getMessage();
			}
		}
		
		public function testLoadBothCusotomAndNimble() {
			Nimble::plugins('form_helper', 'test_plugin');
			$this->Nimble->__construct();
			$test_class = new TestPlugin();
			$this->assertEquals($test_class->foo(), 'foo');
			try{
				new FormHelper();
				$this->assertEquals(1,1);
			} catch(Exception $e) {
				echo $e->getMessage();
			}
		}
		
		public function testLoadPluginAtController() {
			/* test controller is below */
			$klass = new TestController($this);
			$test_class = new TestPlugin();
			$this->assertEquals($test_class->foo(), 'foo');
		}

}


/* test controller */
class TestController extends Controller {
	public function __construct($test) {
		$this->load_plugins('test_plugin');
		$test_class = new TestPlugin();
		$test->assertEquals($test_class->foo(), 'foo');
	}
}

?>