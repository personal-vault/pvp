<?php

declare(strict_types=1);

namespace App\Task;

use InvalidArgumentException;
use App\Repository\FileRepository;
use App\Scan\DirectoryScan;
use App\Scan\FileRemoved;
use RoadRunner\Logger\Logger;

class ScanTask
{
    private string $storage = '/vault';

    public function __construct(
        private DirectoryScan $directory_scan,
        private FileRemoved $file_removed,
        private FileRepository $file_repository,
        private Logger $logger
    ) {}

    public function setStorage(string $storage): void
    {
        if (!file_exists($storage)) {
            throw new InvalidArgumentException('Invalid storage path: ' . $storage);
        }
        if (!is_dir($storage)) {
            throw new InvalidArgumentException('Invalid storage path: ' . $storage);
        }
        $this->storage = $storage;
    }

    public function run(string $id, string $payload): void
    {
        $settings = json_decode($payload);
        assert($settings->filename !== null && is_string($settings->filename));
        $file_path = $this->storage . $settings->filename;

        // See if file is missing (has been moved or deleted)
        if (file_exists($file_path) === false) {
            $this->file_removed->process($file_path);
            return;
        }

        // See if file is a directory
        if (is_dir($file_path)) {
            $this->directory_scan->process($file_path);
            return;
        }

        // Calculate the hash
        $hash = $this->calculate_hash($file_path);

        // Get the database rows that have the same hash or the same file path
        $files = $this->file_repository->findByHashOrPath($hash, $file_path);

        if (count($files) === 0) {
            // File Created
            // TODO: Handle file created
            // Insert DB row
            // Dispatch analyze job
            return;
        }

        if ($files[0]->hash === $hash) {
            if ($files[0]->path !== $file_path) {
                // File Moved / Renamed

                // Copy row to new path

            }
            // Dispatch analyze job
            return;
        }

        if ($files[0]->path === $file_path) {
            // Same file, different hash => File Updated
            // Update DB row, SET removed_at = null

            // Dispatch analyze job
        }

        // $attributes = $this->extract_attributes($file_path)[0];

        // $this->logger->info('|> ' . $hash . ' ' . sprintf('%02.2f ', $time) . $file_path . ' ' . $attributes->MIMEType . PHP_EOL);

        // // Save to database
        // $this->file_repository->insertIfNotExist(
        //     $hash,
        //     $file_path,
        //     $attributes->FileName,
        //     filesize($file_path),
        //     $attributes->MIMEType
        // );
    }

    private function calculate_hash($file_path) {
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
