<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Parser\Parser;
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
        private LoggerInterface $logger,
        private Parser $parser
    ) {}

    public function process(string $path, ?string $hash = null): void
    {
        assert($hash !== null, 'Hash must be set for FileCreated event');

        // Check if row exists in the DB. If it does, then return.
        $files = $this->file_repository->findByHashOrPath(null, $path);
        if (count($files) > 0) {
            $this->logger->warning(__CLASS__ . '::' . __METHOD__ . '(' . $path .') File already exists in DB!');
            return;
        }

        if (!file_exists($path)) {
            $this->logger->warning(__CLASS__ . '::' . __METHOD__ . '(' . $path .') File does not exist on storage!');
            return;
        }

        // Parse file
        $file = $this->parser->parse($path, $hash);
        $file->scanned_at = date('Y-m-d H:i:s');
        $file->scan_version = self::VERSION;
        // Insert into DB
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
