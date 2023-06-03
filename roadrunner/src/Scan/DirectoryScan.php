<?php declare(strict_types=1);

namespace App\Scan;

use App\Task\ScanTask;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spiral\RoadRunner\Jobs\JobsInterface;

/**
 * Handles an event when a directory is scanned.
 */
class DirectoryScan implements ScanInterface
{
    public function __construct(
        private JobsInterface $jobs,
        private LoggerInterface $logger
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash === null, 'Hash must be null for DirectoryScan event');

        if (!file_exists($path)) {
            $this->logger->warning(__CLASS__ . '::' . __METHOD__ . '(' . $path .') Path does not exist!');
            return;
        }

        if (!is_dir($path)) {
            $this->logger->warning(__CLASS__ . '::' . __METHOD__ . '(' . $path .') Path is not a directory!');
            return;
        }

        $queue = $this->jobs->connect('consumer');

        foreach ($this->walkDirectory($path) as $file_path) {
            $this->logger->info($file_path . PHP_EOL);
            $task = $queue->create(
                ScanTask::class,
                payload: \json_encode(['filename' => (string) $file_path])
            );
            $queue->dispatch($task);
        }
    }

    /**
     * @return iterable<string>
     */
    private function walkDirectory(string $path): iterable
    {
        $directory_iterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory_iterator);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                yield $file->getRealPath();
            }
        }
    }
}
