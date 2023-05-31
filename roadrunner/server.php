<?php

declare(strict_types=1);

use League\Container\ReflectionContainer;
use App\Database;
use League\Route\Http\Exception\NotFoundException;
use Meorelia\Repository\File;
use Nyholm\Psr7;
use Nyholm\Psr7\Response;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use RoadRunner\Logger\Logger;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner;

require_once(dirname(__FILE__) . '/vendor/autoload.php');

// Create container and router
$container = new League\Container\Container();
$container->delegate(new ReflectionContainer());
$strategy = (new League\Route\Strategy\ApplicationStrategy)->setContainer($container);

$router   = (new League\Route\Router)->setStrategy($strategy);

// Define routes
require_once(dirname(__FILE__) . '/routes.php');

// Add implementations to container
$rpc = RPC::create('tcp://127.0.0.1:6001');
$container->add(Logger::class)->addArgument($rpc)->setShared(true);
// $container->add(Database::class)->setShared(true);
// $container->add(File::class);

$env = Environment::fromGlobals();
$isJobsMode = $env->getMode() === Mode::MODE_JOBS;

if ($isJobsMode) {
    $consumer = new Consumer();

    $count = 0;
    /** @var ReceivedTaskInterface $task */
    while ($task = $consumer->waitTask()) {
        $shouldBeRestarted = false;
        $action = $container->get($task->getName());
        try {
            $action->run($task->getId(), $task->getPayload());
            $task->complete();
        } catch (\Throwable $e) {
            $task
                ->withHeader('attempts', (string) ((int) $task->getHeaderLine('attempts') - 1))
                ->withHeader('retry-delay', (string) ((int) $task->getHeaderLine('retry-delay') * 2))
                ->fail($e, $shouldBeRestarted);
        } finally {
            $count++;
            if ($count > 100) {
                return;
            }
        }
    }
} else {
    $psrFactory = new Psr7\Factory\Psr17Factory();
    $worker = RoadRunner\Worker::create();
    $psr7 = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

    $count = 0;
    while (true) {
        try {
            $request = $psr7->waitRequest();
        } catch (\Throwable $e) {
            // Although the PSR-17 specification clearly states that there can be
            // no exceptions when creating a request, however, some implementations
            // may violate this rule. Therefore, it is recommended to process the
            // incoming request for errors.
            //
            // Send "Bad Request" response.
            $psr7->respond(new Response(400));
            continue;
        }

        if ($request === null) {
            // We are probably in debug mode so each request only lives once (YOLO mode)
            return;
        }

        try {
            $response = $router->dispatch($request);

            $count++;
            if ($count > 10) {
                $psr7->getWorker()->stop();
                return;
            }

            $psr7->respond($response);
        } catch (NotFoundException $e) {
            // https://datatracker.ietf.org/doc/html/rfc7807 Problem JSON errors
            $psr7->respond(new Response(
                404,
                ['Content-Type' => 'application/problem+json; charset=utf-8'],
                json_encode([
                    'type' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404',
                    'title' => 'Not Found',
                    'status' => 404,
                    'detail' => 'The requested resource could not be found. ¯\_(ツ)_/¯'
                ])
            ));
        } catch (\Throwable $e) {
            // Additionally, we can inform the RoadRunner that the processing
            // of the request failed.
            // Reply by the 500 Internal Server Error response
            $container->get(Logger::class)->error((string) $e);
            $psr7->respond(new Response(500, [], 'Something Went Wrong: ' . (string) $e));

            $psr7->getWorker()->error((string) $e);
        }
    }
}
