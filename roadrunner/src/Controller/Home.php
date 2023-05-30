<?php

declare(strict_types=1);

namespace Memorelia\Controller;

use Memorelia\Repository\File;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RoadRunner\Logger\Logger;

class Home {

    public function __construct(
        private File $file,
        private Logger $logger
    ) {}

    public function getMethod(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response)
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $this->logger->info('Home controller called');

        $this->file->insertIfNotExist('abcd', '/cucu', 'umu.txt', 123, 'text/plain');

        $response->getBody()->write(json_encode($request->getQueryParams()));

        return $response;
    }
}
