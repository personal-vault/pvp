<?php

declare(strict_types=1);

namespace Memorelia\Task;

use InvalidArgumentException;
use RoadRunner\Logger\Logger;
use RuntimeException;

class ScanFile
{
    public function __construct(
        private Logger $logger
    ) {}

    public function run(string $id, string $payload): void
    {
        $settings = json_decode($payload);
        assert($settings->filename !== null && is_string($settings->filename));
        $start = microtime(true);
        $checksum = $this->calculate_checksum($settings->filename);
        $time = microtime(true) - $start;
        $this->logger->info('|> ' . $checksum . ' ' . sprintf('%02.2f ', $time) . $settings->filename . ' ' . PHP_EOL);
    }

    private function calculate_checksum($file_path) {
        $output = '';
        $exit_code = null;
        exec('sha256sum ' . escapeshellarg($file_path), $output, $exit_code);
        if ($exit_code !== 0) {
            throw new InvalidArgumentException('Invalid file path: ' . $file_path);
        }
        return explode(' ', $output[0])[0];
    }
}
