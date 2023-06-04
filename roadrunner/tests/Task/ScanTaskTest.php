<?php declare(strict_types=1);

namespace App\Task;

use App\Model\File;
use App\Repository\FileRepository;
use App\Scan\DirectoryScan;
use App\Scan\FileCreated;
use App\Scan\FileMoved;
use App\Scan\FileRemoved;
use App\Scan\FileUpdated;
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

    public function testItWillProcessFileCreatedIfNewFileDoesNotExistInTheDB()
    {
        // Create random temporary file
        $random_file = tempnam(sys_get_temp_dir(), uniqid('pvp-')) . '.txt';
        file_put_contents($random_file, uniqid('hello-world', true));

        // Mock the FileCreated service
        $file_created = $this->createMock(FileCreated::class);
        $file_created->expects($this->once())
            ->method('process')
            ->with($random_file);
        $this->container->add(FileCreated::class, $file_created);

        $scan_file_task = $this->container->get(ScanTask::class);
        $scan_file_task->setStorage(sys_get_temp_dir());

        // The filename should be just the path+file without the sys_get_temp_dir() prefix
        $filename = substr($random_file, strlen(sys_get_temp_dir()));
        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $filename]))
        );
    }

    public function testItWillProcessFileMovedIfFileNameOrLocationChanged()
    {
        // Create random temporary file
        $random_file = tempnam(sys_get_temp_dir(), uniqid('pvp-'));
        file_put_contents($random_file, uniqid('hello-world', true));
        $hash = hash_file('sha256', $random_file);

        // Create DB file with the same hash, but different path
        $file = new File($hash, $random_file . '.tgz');
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        // Mock the FileMoved service
        $file_moved = $this->createMock(FileMoved::class);
        $file_moved->expects($this->once())
            ->method('process')
            ->with($random_file);
        $this->container->add(FileMoved::class, $file_moved);

        $scan_file_task = $this->container->get(ScanTask::class);
        $scan_file_task->setStorage(sys_get_temp_dir());

        // The filename should be just the path+file without the sys_get_temp_dir() prefix
        $filename = substr($random_file, strlen(sys_get_temp_dir()));
        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $filename]))
        );
    }

    public function testItWillProcessFileUpdatedIfPathIsTheSameButHashChanged()
    {
        // Create random temporary file
        $random_file = tempnam(sys_get_temp_dir(), uniqid('pvp-'));
        file_put_contents($random_file, uniqid('hello-world', true));

        // Create DB file with a different hash
        $file = new File(uniqid('hash-'), $random_file);
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        // Mock the FileMoved service
        $file_updated = $this->createMock(FileUpdated::class);
        $file_updated->expects($this->once())
            ->method('process')
            ->with($random_file);
        $this->container->add(FileUpdated::class, $file_updated);

        $scan_file_task = $this->container->get(ScanTask::class);
        $scan_file_task->setStorage(sys_get_temp_dir());

        // The filename should be just the path+file without the sys_get_temp_dir() prefix
        $filename = substr($random_file, strlen(sys_get_temp_dir()));
        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $filename]))
        );
    }
}
