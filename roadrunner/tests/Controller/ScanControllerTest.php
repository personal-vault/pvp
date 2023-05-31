<?php declare(strict_types=1);

namespace App\Controller;

use Nyholm\Psr7\ServerRequest;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunner\Jobs\Task\PreparedTaskInterface;
use Test\TestCase;

final class ScanControllerTest extends TestCase
{
    public function testItDispatchesScanJob(): void
    {
        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->once())
            ->method('create')
            ->willReturn($this->createMock(PreparedTaskInterface::class));
        $queue->expects($this->once())
            ->method('dispatch');
        $jobs = $this->createMock(JobsInterface::class);
        $jobs->expects($this->once())
            ->method('connect')
            ->with('consumer')
            ->willReturn($queue);

        $this->container->add(JobsInterface::class, $jobs);

        $controller = $this->container->get(ScanController::class);

        $request = new ServerRequest('POST', '/scan/%2Frandom');
        $response = $controller->postMethod($request->withAttribute('path', '/random'));

        $this->assertSame('{"path":"\/random"}', (string) $response->getBody());
    }
}
