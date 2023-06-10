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
class FileMoved implements ScanInterface
{
    public function __construct(
        private FileRepository $file_repository,
        private JobsInterface $jobs,
        private LoggerInterface $logger,
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash !== null, 'Hash must be set for FileMoved event');

        // Get the database rows that have the same hash
        $files = $this->file_repository->findByHash($hash);

        if (count($files) === 0) {
            $this->logger->info(__CLASS__ . '::' . __METHOD__ . '(' . $path .') Cannot find moved file by hash!');
            return;
        }

        $file = reset($files);
        $file->path = $path;
        $file->filename = basename($path);
        //TODO: parse file to update filesize, mime, date_created, gps_lat, gps_lon, gps_alt etc.
        $file->scanned_at = null;
        $file->removed_at = null;
        $this->file_repository->create($file);

        // Dispatch analyze job
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            AnalyzeTask::class,
            payload: \json_encode(['filename' => (string) $path])
        );
        $queue->dispatch($task);
    }
}
