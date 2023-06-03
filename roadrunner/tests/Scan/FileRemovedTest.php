<?php declare(strict_types=1);

namespace App\Scan;

use App\Logger\NullLogger;
use App\Model\File;
use App\Repository\FileRepository;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunner\Jobs\Task\PreparedTaskInterface;
use Test\TestCase;

class FileRemovedTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->container->add(LoggerInterface::class, new NullLogger());
    }

    public function testItSetsDatabaseRowToRemoved(): void
    {
        // Create a file in the database only
        $path = uniqid('/vault/file-path-') . '.txt';
        $file = new File(uniqid('hash'), $path);
        /** @var FileRepository::class */
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        // Expect analyze job to be dispatched
        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->exactly(1))
            ->method('create')
            ->willReturn($this->createMock(PreparedTaskInterface::class));
        $queue->expects($this->exactly(1))
            ->method('dispatch');
        $jobs = $this->createMock(JobsInterface::class);
        $jobs->expects($this->once())
            ->method('connect')
            ->with('consumer')
            ->willReturn($queue);
        $this->container->add(JobsInterface::class, $jobs);

        $file_removed = $this->container->get(FileRemoved::class);

        $this->assertNull(
            $file_removed->process($path)
        );

        // Check that the file was set to removed
        $files = $file_repository->findByHashOrPath(null, $path);
        $this->assertCount(1, $files);
        $this->assertTrue($files[0]->isRemoved());
    }
}
