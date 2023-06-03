<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use InvalidArgumentException;
use Spiral\RoadRunner\Jobs\JobsInterface;

/**
 * Handles an event when a file is created.
 */
class FileCreated implements ScanInterface
{
    public function __construct(
        private FileRepository $file_repository,
        private JobsInterface $jobs
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash !== null, 'Hash must be set for FileCreated event');
        // Check if row exists in the DB. If it does, then return.

        // Insert row into database
        $file = new File($hash, $path);

        // Dispatch analyze job
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            AnalyzeTask::class,
            payload: \json_encode(['filename' => (string) $path])
        );
        $queue->dispatch($task);
    }
}
