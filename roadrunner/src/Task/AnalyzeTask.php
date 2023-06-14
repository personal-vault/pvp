<?php

declare(strict_types=1);

namespace App\Task;

use App\Model\File;
use App\Repository\FileRepository;
use League\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class AnalyzeTask implements TaskInterface
{
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
            // Run analyzers based on the mime filetype
            $this->process($file);
        }

        //TODO: Trigger next task processor
    }

    private function process(File $file): void
    {
        if (empty($file->mime)) {
            $this->logger->info('Analyze without mime for file ID ' . $file->id);
            return;
        }

        $file->analyzed_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        // Convert mime to class name
        $class_name = '\\App\\Analyze\\' . str_replace(' ', '\\', ucwords(str_replace('/', ' ', $file->mime)));

        if ($this->container->has($class_name) === false) {
            $this->logger->warning('Analyze does not have class ' . $class_name);
            $this->file_repository->updateById($file->id, $file);
            return;
        }

        $object = $this->container->get($class_name);
        $object->analyze($file);

        $this->file_repository->updateById($file->id, $file);
    }
}
