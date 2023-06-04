<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
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
        private LoggerInterface $logger,
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash !== null, 'Hash must be set for FileUpdated event');

        // Get the database rows that have the same path
        $files = $this->file_repository->findByHashOrPath(null, $path);

        if (count($files) === 0) {
            $this->logger->info(__CLASS__ . '::' . __METHOD__ . '(' . $path .') Cannot find updated file by path!');
            return;
        }

        // Update DB row, SET removed_at = null
        $file = reset($files);
        $file->hash = $hash;
        $file->removed_at = null;
        //TODO: update the other fields
        $this->file_repository->updateByPath($path, $file);

        // Dispatch analyze job
        $queue = $this->jobs->connect('consumer');
        $task = $queue->create(
            AnalyzeTask::class,
            payload: \json_encode(['filename' => (string) $path])
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
