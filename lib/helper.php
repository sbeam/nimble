<?php

/**
 * Quickly define a new route.
 * A new Route can be defined in two ways:
 *
 * either by passing all of the route parameters as parameters to R():
 *   R('','TestController','index','GET');
 *
 * or by using the methods of the Route class:
 *   R('')->controller("TestController")->action("index")->on("GET");
 *
 * (thanks to:  Rafael S. Souza <rafael.ssouza [__at__] gmail.com>)
 */
function R($pattern)
{
    if (count($args = func_get_args()) == 4)
    {
        $r = new Route($args[0]);
        $r->controller($args[1])->action($args[2])->on($args[3]);
        return $r;
    } else {
        return new Route($pattern);
    }
}

?>