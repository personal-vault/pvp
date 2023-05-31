<?php declare(strict_types=1);

namespace App\Controller;

use Nyholm\Psr7\ServerRequest;
use Test\TestCase;

final class ScanControllerTest extends TestCase
{
    public function testGreetsWithName(): void
    {
        $controller = $this->container->get(ScanController::class);

        $response = $controller->postMethod(new ServerRequest('POST', '/'));

        $this->assertSame('{"all":"good"}', (string) $response->getBody());
    }
}
