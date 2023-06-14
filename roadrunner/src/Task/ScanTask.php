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
use DateTimeImmutable;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ScanTask implements TaskInterface
{
    private ContainerInterface $container;

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

    public function container(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function run(string $id, string $payload): void
    {
        $settings = json_decode($payload);
        assert($settings->filename !== null && is_string($settings->filename));
        $path = $settings->filename;

        // See if "path" is missing (has been moved or deleted)
        if (file_exists($path) === false) {
            $this->logger->info('ScanTask: Processing removed file ' . $path);
            $this->file_removed->process($path);
            return;
        }

        // See if "path" is a directory
        if (is_dir($path)) {
            $this->logger->info('ScanTask: Processing directory ' . $path);
            $this->directory_scan->process($path);
            return;
        }

        // See if there is content to process
        if (filesize($path) === 0) {
            $this->logger->info('ScanTask: Skipping empty file ' . $path);
            return;
        }

        $path_file = $this->file_repository->findByPath($path);
        $is_same_file = $path_file !== null;

        if ($path_file !== null && $path_file->scanned_at !== null) {
            // if scanned sooner than 24 hours ago, skip
            $timestamp = (new DateTimeImmutable($path_file->scanned_at))->getTimestamp();
            if ($timestamp > (time() - 86400)) {
                $this->logger->info('ScanTask: Skipping recently scanned file ' . $path);
                return;
            }
        }

        // Calculate the hash
        $hash = $this->calculate_hash($path);
        $same_hash_files = $this->file_repository->findByHash($hash);

        if ($is_same_file === false) {
            if (count($same_hash_files) === 0) {
                $this->logger->info('ScanTask: Processing created file ' . $path);
                $this->file_created->process($path, $hash);
                return;
            }

            if (count($same_hash_files) > 0) {
                // File Moved / Renamed
                $this->logger->info('ScanTask: Processing copied/moved file ' . $path);
                $this->file_moved->process($path, $hash);
                return;
            }
        }

        $is_same_file_and_hash = count(array_filter($same_hash_files, fn($file) => $file->path === $path)) > 0;
        if ($is_same_file_and_hash === true) {
            // File re-added
            $this->logger->info('ScanTask: Processing existing/re-created file ' . $path);
            $this->file_recreated->process($path, $hash);
            return;
        }
        // Same file, different hash => File Updated
        $this->logger->info('ScanTask: Processing updated file ' . $path);
        $this->file_updated->process($path, $hash);
        return;

        $this->logger->warning('ScanTask: No action taken for file ' . $path);
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
