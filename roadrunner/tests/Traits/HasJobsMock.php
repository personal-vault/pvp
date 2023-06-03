<?php declare(strict_types=1);

namespace Test\Traits;

use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunner\Jobs\Task\PreparedTaskInterface;

trait HasJobsMock {
    private function injectQueueExpectation(int $times = 1): void
    {
        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->exactly($times))
            ->method('create')
            ->willReturn($this->createMock(PreparedTaskInterface::class));
        $queue->expects($this->exactly($times))
            ->method('dispatch');
        $jobs = $this->createMock(JobsInterface::class);
        $jobs->expects($this->once())
            ->method('connect')
            ->with('consumer')
            ->willReturn($queue);

        $this->container->add(JobsInterface::class, $jobs);
    }
}
