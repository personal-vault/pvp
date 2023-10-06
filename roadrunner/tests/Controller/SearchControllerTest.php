<?php declare(strict_types=1);

namespace App\Controller;

use Nyholm\Psr7\ServerRequest;
use Test\TestCase;

final class SearchControllerTest extends TestCase
{
    public function testSearchesForAString(): void
    {
        $controller = $this->container->get(SearchController::class);

        $request = new ServerRequest('GET', '/search');
        $response = $controller->getMethod($request->withQueryParams(['query' => 'make']));

        $this->assertSame('{"path":"\/random"}', (string) $response->getBody());
    }
}
