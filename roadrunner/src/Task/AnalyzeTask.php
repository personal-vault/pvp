<?php

declare(strict_types=1);

namespace App\Task;

use App\Analyze\Plaintext;
use App\Model\File;
use App\Repository\FileRepository;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * The Analyze task does the following:
 * - extract plain text content from a given file
 * - index the contents, using the selected indexers
 *
 * Perhaps it might be a good idea to split it in two different
 * tasks (Transcribe and Index).
 */
class AnalyzeTask implements TaskInterface
{
    private const MIME_TOOLS_MAP = [
        'text/plain' => Plaintext::class,
    ];

    private ContainerInterface $container;

    public function __construct(
        private LoggerInterface $logger,
        private FileRepository $file_repository
    ) {}

    public function container(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function run(string $id, string $payload): void
    {
        $settings = json_decode($payload);
        assert($settings->file_id !== null && is_int($settings->file_id) && $settings->file_id > 0);

        $this->logger->info('Analyze task called, payload: ' . json_encode($payload));

        // Load the File entry from the database
        $file = $this->file_repository->findById($settings->file_id);

        if ($file->isRemoved() === false) {
            $this->extractMeta($file);
            $this->transcribe($file);
        }

        // Load a fresh copy
        $file = $this->file_repository->findById($settings->file_id);

        $this->index($file);

        //TODO: mark as analyzed if the above succeed.
    }

    /**
     * Run the exiftool to extract basic data about this file
     */
    private function extractMeta(File $file): void
    {
        $output = shell_exec('exiftool -json ' . escapeshellarg($file->path));
        $meta = json_decode($output)[0];

        $file->mime = $meta->MIMEType;
        $this->file_repository->updateById($file->id, $file);
    }

    /**
     * Extract transcription for the given $file
     */
    private function transcribe(File $file): void
    {
        if (empty($file->mime)) {
            $this->logger->info('Analyze without mime for file ID ' . $file->id);
            return;
        }

        if (!isset(self::MIME_TOOLS_MAP[$file->mime])) {
            $this->logger->warning('Analyze does not have mapping for MIME ' . $file->mime);
            return;
        }

        $analyzer = $this->container->get(self::MIME_TOOLS_MAP[$file->mime]);

        $file->transcript = $analyzer->analyze($file);
        // This should not be here, but after the indexing part
        // or we should split in transcribed_at and indexed_at (TBD)
        $file->analyzed_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->file_repository->updateById($file->id, $file);
    }

    /**
     * Index the given file
     */
    private function index(File $file): void
    {
        //TODO: implement me
    }
}
