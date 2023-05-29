<?php

declare(strict_types=1);

use League\Container\ReflectionContainer;
use Nyholm\Psr7;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use RoadRunner\Logger\Logger;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

// Create container and router
$container = new League\Container\Container();
$container->delegate(new ReflectionContainer());
$strategy = (new League\Route\Strategy\ApplicationStrategy)->setContainer($container);

$router   = (new League\Route\Router)->setStrategy($strategy);

// Define routes
$router->get('/', 'Acme\Controller\Home::getMethod');

// Add implementations to container
$rpc = RPC::create('tcp://127.0.0.1:6001');
$container->add(Logger::class)->addArgument($rpc)->setShared(true);




/*
// Create new RoadRunner worker from global environment
$worker = Worker::create();

// Create common PSR-17 HTTP factory
$factory = new Psr17Factory();

$psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

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

    try {
        $response = $router->dispatch($request);
        // Here is where the call to your application code will be located.
        // For example:
        //  $response = $app->send($request);
        //
        // Reply by the 200 OK response
        $psr7->respond($response);
    } catch (\Throwable $e) {
        // In case of any exceptions in the application code, you should handle
        // them and inform the client about the presence of a server error.
        //
        // Reply by the 500 Internal Server Error response
        $psr7->respond(new Response(500, [], 'Something Went Wrong!'));

        // Additionally, we can inform the RoadRunner that the processing
        // of the request failed.
        $psr7->getWorker()->error((string)$e);
    }
}*/

$logger = $container->get(Logger::class);

$env = Environment::fromGlobals();

$isJobsMode = $env->getMode() === Mode::MODE_JOBS;

if ($isJobsMode) {
    $consumer = new Consumer();

    $count = 0;
    /** @var ReceivedTaskInterface $task */
    while ($task = $consumer->waitTask()) {
        // $logger->info('Task: ' . $task->getId() . ' ' . $task->getName() . ' ' . $task->getPayload());
        $action = $container->get($task->getName());
        try {
            $action->run($task->getId(), $task->getPayload());
            $task->complete();
        } catch (\Throwable $e) {
            $task->fail($e, $shouldBeRestarted);
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
    while ($request = $psr7->waitRequest()) {
        try {
            $response = $router->dispatch($request);

            $count++;
            if ($count > 10) {
                $psr7->getWorker()->stop();
                return;
            }

            $psr7->respond($response);
        } catch (\Throwable $e) {
            $psr7->getWorker()->error((string) $e);
        }
    }
}
