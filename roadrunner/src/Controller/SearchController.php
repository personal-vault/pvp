<?php

declare(strict_types=1);

namespace App\Controller;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class SearchController {

    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function getMethod(ServerRequestInterface $request): ResponseInterface
    {
        // $path = urldecode($request->getAttribute('path'));
        $response = (new Response)
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $this->logger->info('Search controller called');

        $response->getBody()->write(json_encode(['all' => 'good']));

        return $response;
    }
}
