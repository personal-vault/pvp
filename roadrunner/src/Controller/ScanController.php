<?php

declare(strict_types=1);

namespace App\Controller;

use App\Task\ScanTask;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RoadRunner\Logger\Logger;
use Spiral\RoadRunner\Jobs\JobsInterface;

class ScanController {

    public function __construct(
        private JobsInterface $jobs,
        private Logger $logger
    ) {}

    public function postMethod(ServerRequestInterface $request): ResponseInterface
    {
        $path = urldecode($request->getAttribute('path'));
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            ScanTask::class,
            payload: \json_encode(['filename' => $path])
        );
        $queue->dispatch($task);

        $response = (new Response)
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $this->logger->info('Scan controller called');

        $response->getBody()->write(json_encode([
            'path' => $path,
        ]));

        return $response;
    }
}
