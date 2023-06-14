<?php declare(strict_types=1);

namespace App\Task;

use App\Model\File;
use App\Repository\FileRepository;
use App\Scan\DirectoryScan;
use App\Scan\FileCreated;
use App\Scan\FileMoved;
use App\Scan\FileRecreated;
use App\Scan\FileRemoved;
use App\Scan\FileUpdated;
use DateTimeImmutable;
use Test\TestCase;

final class AnalyzeTaskTest extends TestCase
{
    public function testItAnalyzesViaMimeClass()
    {
        $random_file = tempnam(sys_get_temp_dir(), uniqid('pvp-'));
        file_put_contents($random_file, uniqid('hello-world', true));
        $hash = hash_file('sha256', $random_file);

        // Create DB file with a different hash
        $file = new File($hash, $random_file);
        $file->mime = 'text/plain';
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        $scan_file_task = $this->container->get(AnalyzeTask::class);
        $scan_file_task->container($this->container);

        $this->assertNull(
            $scan_file_task->run('id', json_encode(['file_id' => $file->id]))
        );
    }
}
