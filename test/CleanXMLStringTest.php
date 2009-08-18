<?php

require_once('PHPUnit/Framework.php');

function clean_xml_string($source) {
  $source = str_replace(
    array("&nbsp;", "&mdash;", "&ndash;"),
    array(" ", "--", "-"),
    $source
  );
  $source = preg_replace_callback('#&[^\;]+;#', 'callback_replace_non_xml_entities', $source);
  return $source;
}

function callback_replace_non_xml_entities($matches) {
  return (in_array($matches[0], array("&quot;", "&amp;", "&apos;", "&lt;", "&gt;")) ? $matches[0] : "");
}

class CleanXMLStringTest extends PHPUnit_Framework_TestCase {
  function providerTestCleanXMLString() {
    return array(
      array("&nbsp;", " "),
      array("&mdash;", "--"),
      array("&ndash;", "-"),
      array("&nbsp;&nbsp;", "  "),
      array("&amp;", "&amp;"),
      array("&amp;nbsp;", "&amp;nbsp;"),
      array("&amp;&egrave;&nbsp;", "&amp; "),
    );
  }
  
  /**
   * @dataProvider providerTestCleanXMLString
   */
  function testCleanXMLString($input, $expected_output) {
    $this->assertEquals($expected_output, clean_xml_string($input));
  } 
}

?>