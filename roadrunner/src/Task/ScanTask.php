<?php

declare(strict_types=1);

namespace App\Task;

use InvalidArgumentException;
use App\Repository\FileRepository;
use App\Scan\DirectoryScan;
use App\Scan\FileCreated;
use App\Scan\FileMoved;
use App\Scan\FileRecreated;
use App\Scan\FileRemoved;
use App\Scan\FileUpdated;
use Psr\Log\LoggerInterface;

class ScanTask implements TaskInterface
{
    public function __construct(
        private DirectoryScan $directory_scan,
        private FileCreated $file_created,
        private FileMoved $file_moved,
        private FileRecreated $file_recreated,
        private FileRemoved $file_removed,
        private FileRepository $file_repository,
        private FileUpdated $file_updated,
        private LoggerInterface $logger
    ) {}

    public function run(string $id, string $payload): void
    {
        $settings = json_decode($payload);
        assert($settings->filename !== null && is_string($settings->filename));
        $path = $settings->filename;

        // See if file is missing (has been moved or deleted)
        if (file_exists($path) === false) {
            $this->logger->info('ScanTask: Processing removed file ' . $path);
            $this->file_removed->process($path);
            return;
        }

        // See if file is a directory
        if (is_dir($path)) {
            $this->logger->info('ScanTask: Processing directory ' . $path);
            $this->directory_scan->process($path);
            return;
        }

        // Calculate the hash
        $hash = $this->calculate_hash($path);

        $this->logger->info('ScanTask Hash: ' . $hash);

        // Get the database rows that have the same hash or the same file path
        $files = $this->file_repository->findByHashOrPath($hash, $path);

        $this->logger->info('ScanTask Files: ' . json_encode($files));

        if (count($files) === 0) {
            // File Created
            $this->logger->info('ScanTask: Processing created file ' . $path);
            $this->file_created->process($path, $hash);
            return;
        }

        if ($files[0]->hash === $hash) {
            if ($files[0]->path !== $path) {
                // File Moved / Renamed
                $this->logger->info('ScanTask: Processing moved file ' . $path);
                $this->file_moved->process($path, $hash);
                return;
            }
            // File re-added
            $this->logger->info('ScanTask: Processing re-created file ' . $path);
            $this->file_recreated->process($path, $hash);
            return;
        }

        if ($files[0]->path === $path) {
            // Same file, different hash => File Updated
            $this->logger->info('ScanTask: Processing updated file ' . $path);
            $this->file_updated->process($path, $hash);
        }

        $this->logger->warning('ScanTask: No action taken for file ' . $path);

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
}
