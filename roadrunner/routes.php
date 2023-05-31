<?php

$router->map('POST', '/init', 'App\Controller\InitController::postMethod');
$router->map('POST', '/scan/{path}', 'App\Controller\ScanController::postMethod');
$router->map('POST', '/parse/{path}', 'App\Controller\ParseController::postMethod');
$router->map('POST', '/index/{path}', 'App\Controller\IndexController::postMethod');
$router->map('POST', '/embed/{path}', 'App\Controller\EmbedController::postMethod');
$router->map('GET', '/search', 'App\Controller\SearchController::getMethod');

return $router;