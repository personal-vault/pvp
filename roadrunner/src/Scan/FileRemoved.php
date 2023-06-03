<?php declare(strict_types=1);

namespace App\Scan;

use App\Repository\FileRepository;
use InvalidArgumentException;
use Spiral\RoadRunner\Jobs\JobsInterface;

/**
 * Handles an event when a file is missing.
 */
class FileRemoved implements ScanInterface
{
    public function __construct(
        private FileRepository $file_repository,
        private JobsInterface $jobs
    ) {}

    public function process(string $path): void
    {
        // Update DB and set path to removed
        $files = $this->file_repository->findByHashOrPath(null, $path);
        if (count($files) === 0) {
            throw new InvalidArgumentException('File not found: ' . $path);
        }

        $this->file_repository->updateRemovedByPath($path, date('Y-m-d H:i:s'));

        // Dispatch analyze job
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            AnalyzeTask::class,
            payload: \json_encode(['filename' => (string) $path])
        );
        $queue->dispatch($task);
    }
}
