<?php

declare(strict_types=1);

namespace App\Controller;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RoadRunner\Logger\Logger;

class Home {

    public function __construct(
        private Logger $logger
    ) {}

    public function getMethod(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response)
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $this->logger->info('Home controller called');

        $response->getBody()->write(json_encode($request->getQueryParams()));

        return $response;
    }
}
