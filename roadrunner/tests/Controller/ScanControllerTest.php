<?php declare(strict_types=1);

namespace App\Controller;

use League\Container\Container;
use League\Container\ReflectionContainer;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use RoadRunner\Logger\Logger;
use Spiral\Goridge\RPC\RPC;

final class ScanControllerTest extends TestCase
{
    private Container $container;
    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer());

        $rpc = RPC::create('tcp://127.0.0.1:6001');
        $this->container->add(Logger::class)->addArgument($rpc)->setShared(true);
    }

    public function testGreetsWithName(): void
    {
        $controller = $this->container->get(ScanController::class);

        $response = $controller->postMethod(new ServerRequest('POST', '/'));

        $this->assertSame('{"all":"good"}', (string) $response->getBody());
    }
}
