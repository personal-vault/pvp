<?php

declare(strict_types=1);

namespace App\Analyze;

use App\Model\File;
use Psr\Log\LoggerInterface;

class Plaintext implements AnalyzeInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function analyze(File $file): ?string
    {
        $this->logger->info('Plain Text analyzer called');

        $file_contents = file_get_contents($file->path);

        // IMPLEMENT ME

        return $file_contents;
    }
}
