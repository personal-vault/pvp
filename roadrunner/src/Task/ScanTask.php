<?php

declare(strict_types=1);

namespace App\Task;

use InvalidArgumentException;
use App\Repository\FileRepository;
use App\Scan\DirectoryScan;
use App\Scan\FileCreated;
use App\Scan\FileMoved;
use App\Scan\FileRemoved;
use Psr\Log\LoggerInterface;

class ScanTask
{
    private string $storage = '/vault';

    public function __construct(
        private DirectoryScan $directory_scan,
        private FileCreated $file_created,
        private FileMoved $file_moved,
        private FileRemoved $file_removed,
        private FileRepository $file_repository,
        private LoggerInterface $logger
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
        $path = $this->storage . $settings->filename;

        // See if file is missing (has been moved or deleted)
        if (file_exists($path) === false) {
            $this->file_removed->process($path);
            return;
        }

        // See if file is a directory
        if (is_dir($path)) {
            $this->directory_scan->process($path);
            return;
        }

        // Calculate the hash
        $hash = $this->calculate_hash($path);

        // Get the database rows that have the same hash or the same file path
        $files = $this->file_repository->findByHashOrPath($hash, $path);

        if (count($files) === 0) {
            // File Created
            $this->file_created->process($path, $hash);
            return;
        }

        if ($files[0]->hash === $hash) {
            if ($files[0]->path !== $path) {
                // File Moved / Renamed
                $this->file_moved->process($path, $hash);
            }
            // Dispatch analyze job
            return;
        }

        if ($files[0]->path === $path) {
            // Same file, different hash => File Updated
            // Update DB row, SET removed_at = null

            // Dispatch analyze job
        }

        // $attributes = $this->extract_attributes($path)[0];

        // $this->logger->info('|> ' . $hash . ' ' . sprintf('%02.2f ', $time) . $path . ' ' . $attributes->MIMEType . PHP_EOL);

        // // Save to database
        // $this->file_repository->insertIfNotExist(
        //     $hash,
        //     $path,
        //     $attributes->FileName,
        //     filesize($path),
        //     $attributes->MIMEType
        // );
    }

    private function calculate_hash($path) {
        $output = '';
        $exit_code = null;
        exec('sha256sum ' . escapeshellarg($path), $output, $exit_code);
        if ($exit_code !== 0) {
            throw new InvalidArgumentException('Invalid file path: ' . $path);
        }
        return explode(' ', $output[0])[0];
    }

    private function extract_attributes($path): array
    {
        $output = '';
        $exit_code = null;
        exec('exiftool -j ' . escapeshellarg($path), $output, $exit_code);
        if ($exit_code !== 0) {
            throw new InvalidArgumentException('Invalid file path: ' . $path);
        }
        return json_decode(implode("\n", $output));
    }
}
