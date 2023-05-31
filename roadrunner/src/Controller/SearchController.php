<?php

declare(strict_types=1);

namespace App\Controller;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RoadRunner\Logger\Logger;

class SearchController {

    public function __construct(
        private Logger $logger
    ) {}

    public function getMethod(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response)
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');

        $this->logger->info('Search controller called');

        $response->getBody()->write(json_encode(['all' => 'good']));

        return $response;
    }
}
