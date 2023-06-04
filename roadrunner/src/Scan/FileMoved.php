<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;

/**
 * Handles an event when a file is created.
 */
class FileMoved implements ScanInterface
{
    public function __construct(
        private FileRepository $file_repository,
        private JobsInterface $jobs,
        private LoggerInterface $logger,
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash !== null, 'Hash must be set for FileCreated event');

        // Check if row exists in the DB. If it does, then return.
        $files = $this->file_repository->findByHashOrPath(null, $path);
        if (count($files) > 0) {
            $this->logger->info(__CLASS__ . '::' . __METHOD__ . '(' . $path .') File already exists in DB!');
            return;
        }

        // Copy DB row with new path (and not removed)
        // $file = new File($hash, $path);
        //TODO: add all the other fields
        // $this->file_repository->create($file);

        // Dispatch analyze job
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            AnalyzeTask::class,
            payload: \json_encode(['filename' => (string) $path])
        );
        $queue->dispatch($task);
    }
}
