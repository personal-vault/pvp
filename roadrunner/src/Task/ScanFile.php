<?php

declare(strict_types=1);

namespace Memorelia\Task;

use RoadRunner\Logger\Logger;

class ScanFile
{
    public function __construct(
        private Logger $logger
    ) {}

    public function run(string $id, string $payload): void
    {
        $settings = json_decode($payload);
        $this->logger->info('ID ' . $id . ' ' . $settings->filename . PHP_EOL);
    }
}
