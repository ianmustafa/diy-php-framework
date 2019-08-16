<?php

$router->get('/[{name}]', 'HelloController@hello');
$router->post('/', 'HelloController@fromPost');
