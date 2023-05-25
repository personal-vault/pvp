<?php

declare(strict_types=1);

use Spiral\RoadRunner;
use Nyholm\Psr7;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

$router = new League\Route\Router;

// map a route
$router->map('GET', '/', function (ServerRequestInterface $request): ResponseInterface {
    $response = (new Psr7\Response)->withStatus(201);
    $response->getBody()->write('<h1>Hello, World!</h1>' . json_encode($request->getAttributes()));
    return $response;
});

$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();

$worker = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

$count = 0;
while ($req = $worker->waitRequest()) {
    try {
        // $rsp = new Psr7\Response();
        // $rsp->getBody()->write('Hello world O.o... ' . $req->getUri()->getPath());
        $rsp = $router->dispatch($req);

        $count++;
        if ($count > 10) {
            $worker->getWorker()->stop();
            return;
        }

        $worker->respond($rsp);
    } catch (\Throwable $e) {
        $worker->getWorker()->error((string)$e);
    }
}
