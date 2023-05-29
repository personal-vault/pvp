<?php

declare(strict_types=1);

namespace Memorelia\Producer;

use Memorelia\Task\ScanFile;
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
            $queue->push(
                ScanFile::class,
                payload: \json_encode(['filename' => (string) $file_path])
            );
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
