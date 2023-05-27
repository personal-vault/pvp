<?php

declare(strict_types=1);

namespace Acme\Controller;

use Nyholm\Psr7\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Home {
    public function getMethod(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response)
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $response->getBody()->write(json_encode($request->getQueryParams()));

        return $response;
    }
}
