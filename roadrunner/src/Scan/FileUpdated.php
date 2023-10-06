<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use App\Task\AnalyzeTask;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;

/**
 * Handles an event when a file is moved.
 *
 * This class handles the new file path, the old file path is handled by FileRemoved.
 */
class FileUpdated implements ScanInterface
{
    public function __construct(
        private FileRepository $file_repository,
        private JobsInterface $jobs,
        private LoggerInterface $logger
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash !== null, 'Hash must be set for FileUpdated event');

        $file = $this->file_repository->findByPath($path);
        if ($file === null) {
            $this->logger->info(__CLASS__ . '::' . __METHOD__ . '(' . $path .') Cannot find updated file by path!');
            return;
        }

        // Re-parse the file
        $new_file = new File($hash, $path);
        $file->scanned_at = date('Y-m-d H:i:s');
        $file->scan_version = self::VERSION;
        $file->name = basename($path);
        $file->size = filesize($path);
        // Update File in DB
        $this->file_repository->updateByPath($path, $new_file);

        // Dispatch analyze job
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            AnalyzeTask::class,
            payload: json_encode(['file_id' => $file->id])
        );
        $queue->dispatch($task);
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
