<?php declare(strict_types=1);

namespace App\Scan;

use App\Task\ScanTask;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Logger;

/**
 * Handles an event when a directory is scanned.
 */
class DirectoryScan implements ScanInterface
{
    public function __construct(
        private Logger $logger,
        private RPC $rpc
    ) {}

    public function process(string $path): void
    {
        $jobs = new Jobs($this->rpc);
        $queue = $jobs->connect('consumer');

        foreach ($this->walkDirectory($path) as $file_path) {
            $this->logger->info($file_path . PHP_EOL);
            $task = $queue->create(
                ScanTask::class,
                payload: \json_encode(['filename' => (string) $file_path])
            );
            $queue->dispatch($task);
        }
    }

    private function walkDirectory($path) {
        $directory_iterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory_iterator);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                yield $file->getRealPath();
            }
        }
    }
}
