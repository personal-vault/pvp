<?php

declare(strict_types=1);

namespace App\Task;

use App\Model\File;
use App\Repository\FileRepository;
use Test\TestCase;

final class AnalyzeTaskTest extends TestCase
{
    public function testItAnalyzesViaMimeClass()
    {
        $random_file = tempnam(sys_get_temp_dir(), uniqid('pvp-'));
        $random_content = uniqid('hello-world', true);
        file_put_contents($random_file, $random_content);
        $hash = hash_file('sha256', $random_file);

        // Create DB file with a different hash
        $file = new File($hash, $random_file);
        $file->mime = 'text/plain';
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        $scan_file_task = $this->container->get(AnalyzeTask::class);
        $scan_file_task->container($this->container);

        // Act
        $scan_file_task->run('id', json_encode(['file_id' => $file->id]));

        // Assert
        $new_file = $file_repository->findById($file->id);
        $this->assertNotNull($new_file->analyzed_at);
        $this->assertNotNull($new_file->transcript);
        $this->assertSame($random_content, $new_file->transcript);
    }

    public function testItSkipsAnalyzerIfMimeTypeNotMapped()
    {
        $file = new File('123', uniqid());
        $file->mime = 'application/ogg';
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        $scan_file_task = $this->container->get(AnalyzeTask::class);
        $scan_file_task->container($this->container);

        // Act
        $scan_file_task->run('id', json_encode(['file_id' => $file->id]));

        // Assert
        $new_file = $file_repository->findById($file->id);
        $this->assertNull($new_file->analyzed_at);
        $this->assertNull($new_file->transcript);
    }
}
