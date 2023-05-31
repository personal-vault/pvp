<?php

$router->map('POST', '/init', 'Memorelia\Controller\InitController::postMethod');
$router->map('POST', '/scan/:path', 'Memorelia\Controller\ScanController::postMethod');
$router->map('POST', '/parse/:path', 'Memorelia\Controller\ParseController::postMethod');
$router->map('POST', '/index/:path', 'Memorelia\Controller\IndexController::postMethod');
$router->map('POST', '/embed/:path', 'Memorelia\Controller\EmbedController::postMethod');
$router->map('GET', '/search', 'Memorelia\Controller\SearchController::getMethod');

return $router;
