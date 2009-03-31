<?php
require_once('PHPUnit/Framework.php');
require_once('../nimble.php');

class TestFileUtils extends PHPUnit_Framework_TestCase {

  public function testFileJoinReturnsString() {
    $string = 'test' . DIRECTORY_SEPARATOR . 'myfolder';
    $this->assertEquals($string, FileUtils::join('test', 'myfolder'));
  }

}

?>