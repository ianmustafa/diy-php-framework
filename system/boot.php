<?php
//== Boot
// Definisikan konstanta yg mengarah ke root folder
define('ROOT', str_replace('system', '', __DIR__));

// Load Composer
require ROOT . 'vendor/autoload.php';

// Load konfig, helper, dll disini


//== Handler
// Panggil route handler
$webRouteHandler = new System\Handlers\RouteHandler(ROOT . 'routes/web.php');
$webRouteHandler->dispatch();

