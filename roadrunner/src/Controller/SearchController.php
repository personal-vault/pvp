<?php

declare(strict_types=1);

namespace App\Controller;

use App\Search\FulltextSearchService;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class SearchController {

    public function __construct(
        private LoggerInterface $logger,
        private FulltextSearchService $fulltext_search_service
    ) {}

    public function getMethod(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response)
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $this->logger->info('Search controller called');

        $params = $request->getQueryParams();
        $query = $params['query'] ?? null;

        $results = $this->fulltext_search_service->runSearch($query);

        $response->getBody()->write(json_encode(['params' => $results]));

        return $response;
    }
}
