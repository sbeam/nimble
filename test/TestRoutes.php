<?php

require_once('PHPUnit/Framework.php');
require_once('../NiceDog.php');

class TestRoutes extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->nicedog = NiceDog::getInstance();
        $this->nicedog->routes = array();
    }

    /**
     * @dataProvider providerRubyOnRailsRoutes
     */
    public function testRubyOnRailsRoutes($ror_route, $expected_pattern) {
        $this->nicedog->add_url($ror_route, "Class", "method");
        $this->assertEquals("/^" . str_replace('/', '\/', $expected_pattern) . "$/", $this->nicedog->routes[0][0]);
    }

    public function providerRubyOnRailsRoutes() {
        $pattern = "[a-zA-Z0-9_-]+";

        return array(
            array(":id", "(?P<id>{$pattern})"),
            array("view/:id", "view/(?P<id>{$pattern})"),
            array("view/:id1", "view/(?P<id1>{$pattern})"),
            array("view/:1id", "view/(?P<1id>{$pattern})"),
            array("view/:i_d", "view/(?P<i_d>{$pattern})"),
            array("view/:i-d", "view/(?P<i>{$pattern})-d"),
            array("view/:id/action", "view/(?P<id>{$pattern})/action"),
            array("view/:id/action/:id2", "view/(?P<id>{$pattern})/action/(?P<id2>{$pattern})"),
            array(":id:id2", "(?P<i>{$pattern})d(?P<id2>{$pattern})")
        );
    }

    public function testFormatRoutes() {
        $this->nicedog->url = "test";
        $this->nicedog->add_url('', "Class", "method");
        $this->assertEquals("/^$/", $this->nicedog->routes[0][0]);

        $this->nicedog->routes = array();
        $this->nicedog->url = "test.xml";
        $this->nicedog->add_url('', "Class", "method");
        $this->assertEquals("/^\.(?P<format>[a-zA-Z0-9]+)$/", $this->nicedog->routes[0][0]);
    }
}

?>
