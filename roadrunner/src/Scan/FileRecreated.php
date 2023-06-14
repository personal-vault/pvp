<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use App\Task\AnalyzeTask;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;

/**
 * Handles an event when a file is moved.
 *
 * This class handles the new file path, the old file path is handled by FileRemoved.
 */
class FileRecreated implements ScanInterface
{
    public function __construct(
        private FileRepository $file_repository,
        private JobsInterface $jobs,
        private LoggerInterface $logger,
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash !== null, 'Hash must be set for FileRecreated event');

        $file = $this->file_repository->findByPath($path);

        if ($file === null) {
            $this->logger->warning(__CLASS__ . '::' . __METHOD__ . '(' . $path .') Cannot find file by hash!');
            return;
        }
        if ($file->path != $path) {
            $this->logger->warning(
                __CLASS__ . '::' . __METHOD__ . '(' . $path .') Re-added file path mismatched with ' . $file->path
            );
            return;
        }

        $file->removed_at = null;
        $file->scanned_at = date('Y-m-d H:i:s');
        $this->file_repository->updateByPath($path, $file);

        // Dispatch analyze job
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            AnalyzeTask::class,
            payload: json_encode(['file_id' => $file->id])
        );
        $queue->dispatch($task);
    }
}
