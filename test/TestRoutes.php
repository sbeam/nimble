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
            array("view/:id", "view/(?P<id>{$pattern})")
        );
    }
}

?>
