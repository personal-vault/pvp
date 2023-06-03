<?php declare(strict_types=1);

namespace App\Task;

use App\Scan\DirectoryScan;
use App\Scan\FileRemoved;
use InvalidArgumentException;
use Test\TestCase;

final class ScanTaskTest extends TestCase
{
    public function testItThrowsIfSetStorageToAnInvalidPath()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid storage path: /inexisting-directory');

        $scan_file_task = $this->container->get(ScanTask::class);
        $scan_file_task->setStorage('/inexisting-directory');
    }

    public function testItThrowsIfSetStorageToAFileInsteadOfDirectory()
    {
        $temporary_file = tempnam(sys_get_temp_dir(), uniqid('pvp-'));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid storage path: ' . $temporary_file);
        $scan_file_task = $this->container->get(ScanTask::class);

        $scan_file_task->setStorage($temporary_file);
    }


    public function testItWillProcessFileRemovedScanIfFileDoesNotExist()
    {
        $file_removed = $this->createMock(FileRemoved::class);
        $file_removed->expects($this->once())
            ->method('process')
            ->with('/vault/some-inexisting-file');

        $this->container->add(FileRemoved::class, $file_removed);

        $scan_file_task = $this->container->get(ScanTask::class);

        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => '/some-inexisting-file']))
        );
    }

    public function testItWillNotProcessFileRemovedScanIfFileIsADirectory()
    {
        $random_directory = '/tmp/random-directory';
        @mkdir($random_directory);

        $file_removed = $this->createMock(FileRemoved::class);
        $file_removed->expects($this->never())
            ->method('process')
            ->with($random_directory);

        $this->container->add(FileRemoved::class, $file_removed);

        $scan_file_task = $this->container->get(ScanTask::class);
        $scan_file_task->setStorage('/tmp');

        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => '/random-directory']))
        );

        @rmdir($random_directory);
    }

    public function testItWillDirectoryScanIfPathIsADirectory()
    {
        $random_directory = '/tmp/random-directory';
        @mkdir($random_directory);

        $directory_scan = $this->createMock(DirectoryScan::class);
        $directory_scan->expects($this->once())
            ->method('process')
            ->with($random_directory);

        $this->container->add(DirectoryScan::class, $directory_scan);

        $scan_file_task = $this->container->get(ScanTask::class);
        $scan_file_task->setStorage('/tmp');

        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => '/random-directory']))
        );

        @rmdir($random_directory);
    }
}
