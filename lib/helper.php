<?php


/*
 *  New routes,  just sugar!
 *  R('','Test','index','GET'); turns: 
 *  R('')->controller('test')->action('index')->on('GET');
 * Thanks to:  Rafael S. Souza <rafael.ssouza [__at__] gmail.com>
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