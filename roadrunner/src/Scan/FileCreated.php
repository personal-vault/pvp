<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use App\Task\AnalyzeTask;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;

/**
 * Handles an event when a file is created.
 */
class FileCreated implements ScanInterface
{
    public function __construct(
        private FileRepository $file_repository,
        private JobsInterface $jobs,
        private LoggerInterface $logger
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash !== null, 'Hash must be set for FileCreated event');

        // Check if row exists in the DB. If it does, then return.
        $file = $this->file_repository->findByPath($path);
        if ($file !== null) {
            $this->logger->warning(__CLASS__ . '::' . __METHOD__ . '(' . $path .') File already exists in DB!');
            return;
        }

        if (!file_exists($path)) {
            $this->logger->warning(__CLASS__ . '::' . __METHOD__ . '(' . $path .') File does not exist on storage!');
            return;
        }

        // Parse file
        $file = new File($hash, $path);
        $file->scanned_at = date('Y-m-d H:i:s');
        $file->scan_version = self::VERSION;
        $file->name = basename($path);
        $file->size = filesize($path);
        // Insert into DB
        $this->file_repository->create($file);

        // Dispatch analyze job
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            AnalyzeTask::class,
            payload: json_encode(['file_id' => $file->id])
        );
        $queue->dispatch($task);
    }
}
