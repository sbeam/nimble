<?php
	require_once('PHPUnit/Framework.php');
	require_once('../nimble.php');
	require_once('../lib/support/base.php');
	/**
	* @package FrameworkTest
	*/
	class TestTagHelpers extends PHPUnit_Framework_TestCase {
	
	
		public function setUp() {
			$dir = dirname(__FILE__);
			$this->nimble = Nimble::getINstance();
			Nimble::set_config('stylesheet_folder', FileUtils::join($dir, 'assets', 'stylesheets'));
			Nimble::set_config('stylesheet_folder_url', '/stylesheet');
			Nimble::set_config('javascript_folder', FileUtils::join($dir, 'assets', 'javascript'));
			Nimble::set_config('javascript_folder_url', '/javascript');
		}
		
		
		
		public function testJavascriptTagHelper() {
			$regex = '!\<script\stype\=\"text/javascript\"\ssrc=\"/javascript/java[0-9]\.js\?[0-9]+\"\>\</script\>!';
			$tags = AssetTag::javascript_include_tag('java1', 'java2', 'java3');
			$array = explode("\n", trim($tags));
			$this->assertEquals(count($array), 3);
			foreach($array as $tag) {
				$match = preg_match($regex, $tag);
				$this->assertEquals($match, 1);
			}
		
		}
		
		public function testJavascriptTagHelperUrl() {
	
			$regex = '!\<script\stype\=\"text/javascript\"\ssrc=\"http\://java[0-9]\.js\"\>\</script\>!';
			$tags = AssetTag::javascript_include_tag('http://java1.js', 'http://java2.js', 'http://java3.js');
			$array = explode("\n", trim($tags));
			$this->assertEquals(count($array), 3);
			foreach($array as $tag) {
				$match = preg_match($regex, $tag);
				$this->assertEquals($match, 1);
			}
		
		}
		
		
		public function testStylesheetHelper() {
			$regex = '!\<link\srel\=\"stylesheet\"\stype\=\"text/css\"\smedia\=\"[a-z]+\"\shref\=\"/stylesheet/style[0-9]+\.css\?[0-9]+\"\s/\>!';
			$tags = AssetTag::stylesheet_link_tag('style1', 'style2', 'style3');
			$array = explode("\n", trim($tags));
			$this->assertEquals(count($array), 3);
			foreach($array as $tag) {
				$match = preg_match($regex, $tag);
				$this->assertEquals($match, 1);
			}
		
		}
		
		public function testStylesheetHelperUrl() {
			$regex = '!\<link\srel\=\"stylesheet\"\stype\=\"text/css\"\smedia\=\"[a-z]+\"\shref\=\"http\://style[0-9]+\.css\"\s/\>!';
			$tags = AssetTag::stylesheet_link_tag('http://style1.css', 'http://style2.css', 'http://style3.css');
			$array = explode("\n", trim($tags));
			$this->assertEquals(count($array), 3);
			foreach($array as $tag) {
				$match = preg_match($regex, $tag);
				$this->assertEquals($match, 1);
			}
		
		}
		
		public function testPageTitleCarrier() {
			Nimble::set_title('My Page');
			$this->assertEquals('My Page', Nimble::get_title());
		}
		
		public function testPageTitleCarrierWithDefault() {
			Nimble::set_config('site_title', 'Mysite');
			Nimble::set_title('My Page');
			$this->assertEquals('Mysite:My Page', Nimble::get_title());
		}
		
		public function testPageTitleCarrierWithCustomSeperator() {
			Nimble::set_config('site_title', 'Mysite');
			Nimble::set_config('title_seperator', '->');
			Nimble::set_title('My Page');
			$this->assertEquals('Mysite->My Page', Nimble::get_title());
		}
		
	}

?>