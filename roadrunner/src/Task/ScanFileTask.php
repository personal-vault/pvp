<?php

declare(strict_types=1);

namespace App\Task;

use InvalidArgumentException;
use App\Repository\File;
use RoadRunner\Logger\Logger;

class ScanFileTask
{
    public function __construct(
        private File $file,
        private Logger $logger
    ) {}

    public function run(string $id, string $payload): void
    {
        $settings = json_decode($payload);
        assert($settings->filename !== null && is_string($settings->filename));
        $file_path = '/vault' . $settings->filename;

        $start = microtime(true);
        $checksum = $this->calculate_checksum($file_path);
        $time = microtime(true) - $start;

        $attributes = $this->extract_attributes($file_path)[0];

        $this->logger->info('|> ' . $checksum . ' ' . sprintf('%02.2f ', $time) . $file_path . ' ' . $attributes->MIMEType . PHP_EOL);

        // Save to database
        $this->file->insertIfNotExist(
            $checksum,
            $file_path,
            $attributes->FileName,
            filesize($file_path),
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
