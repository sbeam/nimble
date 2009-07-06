<?php
require_once('PHPUnit/Framework.php');
require_once('../nimble.php');
if(version_compare(PHP_VERSION, '5.3', '>=')) {
	require_once(dirname(__FILE__) . '/model/mailer_test_model.php');
	class MailerTest extends PHPUnit_Framework_TestCase {	
		
		public function setUp() {
			$this->nimble = Nimble::getInstance();
			$this->nimble->config['view_path'] = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'views'));
		}
		
		public function testFooCreates() {
			$string = 'This is from the test';
			$foo = MailerTestModel::create_foo($string);
			$this->assertEquals($foo->from, 'sdavis@stsci.edu');
			$this->assertEquals($foo->recipiants, array('sdavis@stsci.edu'));
			$this->assertEquals($foo->subject, 'WHOA');
			$this->assertTrue(isset($foo->_prepaired_message) && !empty($foo->_prepaired_message));
			$this->assertIncludes($string, $foo->_prepaired_message);
			$this->assertIncludes('PHP TEMPLATE', $foo->_prepaired_message);
			$this->assertIncludes('TEXT TEMPLATE', $foo->_prepaired_message);
			$this->assertTrue(isset($foo->_divider));
		}
		
		public function testBarCreates() {
			$string = 'This is for Bar Test';
			$bar = MailerTestModel::create_bar($string);
			$this->assertTrue(isset($bar->_prepaired_message) && !empty($bar->_prepaired_message));
			$this->assertIncludes($string, $bar->_prepaired_message);
			$this->assertIncludes('BAR TEMPLATE', $bar->_prepaired_message);
			$this->assertEquals($bar->from, 'sdavis@stsci.edu');
			$this->assertEquals($bar->recipiants, array('sdavis@stsci.edu'));
			$this->assertEquals($bar->subject, 'WHOA2');
			
		}
			
		public function assertIncludes($needle, $haystack) {
			$this->assertTrue(strpos($haystack, $needle) !== false);
		}
			
			
	}
	
}

?>