<?php

declare(strict_types=1);

namespace App\Task;

use Psr\Log\LoggerInterface;

class AnalyzeTask implements TaskInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function run(string $id, string $payload): void
    {
        $this->logger->info('Analyze task called');
    }
}
