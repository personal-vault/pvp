<?php

declare(strict_types=1);

namespace Memorelia\Task;

use InvalidArgumentException;
use Memorelia\Repository\File;
use RoadRunner\Logger\Logger;

class ScanFile
{
    public function __construct(
        private File $file,
        private Logger $logger
    ) {}

    public function run(string $id, string $payload): void
    {
        $settings = json_decode($payload);
        assert($settings->filename !== null && is_string($settings->filename));
        $start = microtime(true);
        $checksum = $this->calculate_checksum($settings->filename);
        $time = microtime(true) - $start;

        $attributes = $this->extract_attributes($settings->filename)[0];

        $this->logger->info('|> ' . $checksum . ' ' . sprintf('%02.2f ', $time) . $settings->filename . ' ' . $attributes->MIMEType . PHP_EOL);

        // Save to database
        $this->file->insertIfNotExist(
            $checksum,
            $settings->filename,
            $attributes->FileName,
            filesize($settings->filename),
            $attributes->MIMEType
        );
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

    private function extract_attributes($file_path): array
    {
        $output = '';
        $exit_code = null;
        exec('exiftool -j ' . escapeshellarg($file_path), $output, $exit_code);
        if ($exit_code !== 0) {
            throw new InvalidArgumentException('Invalid file path: ' . $file_path);
        }
        return json_decode(implode("\n", $output));
    }
}
