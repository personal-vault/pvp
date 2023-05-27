<?php

declare(strict_types=1);

use Spiral\RoadRunner;
use Nyholm\Psr7;
use Nyholm\Response;

use Kint\Kint;

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

Kint::$return = true;
Kint::$cli_detection = false;
Kint::$expanded = true;

$router = new League\Route\Router;
$router->get('/', 'Acme\Controller\Home::getMethod');

$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();

$worker = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

$count = 0;
while ($request = $worker->waitRequest()) {
    try {
        $response = $router->dispatch($request);

        $count++;
        if ($count > 10) {
            $worker->getWorker()->stop();
            return;
        }

        $worker->respond($response);
    } catch (\Throwable $e) {
        //TODO: Log error via Monolog

        $response = (new Response())
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response->getBody()->write(Kint::dump($e));
        $worker->respond($response);

        $worker->getWorker()->error((string) $e);
    }
}
