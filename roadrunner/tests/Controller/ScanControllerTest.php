<?php declare(strict_types=1);

namespace App\Controller;

use Nyholm\Psr7\ServerRequest;
use Test\TestCase;
use Test\Traits\HasJobsMock;

final class ScanControllerTest extends TestCase
{
    use HasJobsMock;

    public function testItDispatchesScanJob(): void
    {
        $this->injectQueueExpectation(1);

        $controller = $this->container->get(ScanController::class);

        $request = new ServerRequest('POST', '/scan');
        $response = $controller->postMethod($request->withParsedBody(['path' => '/random']));

        $this->assertSame('{"path":"\/random"}', (string) $response->getBody());
    }
}
