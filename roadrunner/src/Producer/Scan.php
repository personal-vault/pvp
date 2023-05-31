<?php

declare(strict_types=1);

namespace App\Producer;

use App\Task\ScanFileTask;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Logger;

class Scan {

    public function __construct(
        private Logger $logger,
        private RPC $rpc
    ) {}

    public function run(string $storage_path): void
    {
        $jobs = new Jobs($this->rpc);
        $queue = $jobs->connect('consumer');

        foreach ($this->walk_directory($storage_path) as $file_path) {
            $this->logger->info($file_path . PHP_EOL);
            $task = $queue->create(
                ScanFileTask::class,
                payload: \json_encode(['filename' => (string) $file_path])
            );
            $queue->dispatch($task);
        }
    }

    private function walk_directory($path) {
        $directory_iterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory_iterator);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                yield $file->getRealPath();
            }
        }
    }
}
