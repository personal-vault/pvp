<?php

declare(strict_types=1);

namespace App\Controller;

use App\Task\ScanTask;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;

class ScanController {

    public function __construct(
        private JobsInterface $jobs,
        private LoggerInterface $logger
    ) {}

    public function postMethod(ServerRequestInterface $request): ResponseInterface
    {
        $attributes = $request->getParsedBody();
        if (!isset($attributes['path'])) {
            throw new \Exception('Path not set');
        }
        $path = $attributes['path'];
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            ScanTask::class,
            payload: \json_encode(['name' => $path])
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
