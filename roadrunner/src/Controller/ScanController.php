<?php

declare(strict_types=1);

namespace App\Controller;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RoadRunner\Logger\Logger;

class ScanController {

    public function __construct(
        private Logger $logger
    ) {}

    public function postMethod(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response)
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $this->logger->info('Scan controller called');

        $response->getBody()->write(json_encode(['all' => 'good']));

        return $response;
    }
}
