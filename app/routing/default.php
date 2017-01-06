<?php

use Symfony\Component\HttpFoundation\Request;

$routes
    ->match('/post', 'controller.kontact:postAction')
    ->bind('kontact.post')
    ->method(Request::METHOD_POST)
;
