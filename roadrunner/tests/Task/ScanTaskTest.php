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
use Test\TestCase;

final class ScanTaskTest extends TestCase
{
    public function testItWillProcessFileRemovedScanIfFileDoesNotExist()
    {
        $file_removed = $this->createMock(FileRemoved::class);
        $file_removed->expects($this->once())
            ->method('process')
            ->with('/vault/some-inexisting-file');

        $this->container->add(FileRemoved::class, $file_removed);

        $scan_file_task = $this->container->get(ScanTask::class);

        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => '/vault/some-inexisting-file']))
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

        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $random_directory]))
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

        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $random_directory]))
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

        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $random_file]))
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
        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $random_file]))
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

        $file_updated = $this->createMock(FileUpdated::class);
        $file_updated->expects($this->once())
            ->method('process')
            ->with($random_file);
        $this->container->add(FileUpdated::class, $file_updated);

        $scan_file_task = $this->container->get(ScanTask::class);
        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $random_file]))
        );
    }

    public function testItWillProcessAFileCorrectlyIfThereAreOthersOnDisk()
    {
        $random_file_1 = tempnam(sys_get_temp_dir(), uniqid('pvp1-'));
        file_put_contents($random_file_1, uniqid('hello-world1', true));
        $hash_1 = hash_file('sha256', $random_file_1);
        $random_file_2 = tempnam(sys_get_temp_dir(), uniqid('pvp2-'));
        file_put_contents($random_file_2, uniqid('hello-world2', true));
        $hash_2 = hash_file('sha256', $random_file_2);

        $file_1 = new File($hash_1, $random_file_1);
        $file_2 = new File($hash_2, $random_file_2);
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file_1);
        $file_repository->create($file_2);

        $file_recreated = $this->createMock(FileRecreated::class);
        $file_recreated->expects($this->once())
            ->method('process')
            ->with($random_file_1);
        $this->container->add(FileRecreated::class, $file_recreated);

        $scan_file_task = $this->container->get(ScanTask::class);
        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $random_file_1]))
        );
    }

    public function testItWillProcessAFileCorrectlyIfItIsInMultiplePlaces()
    {
        $random_file_1 = tempnam(sys_get_temp_dir(), uniqid('pvp1-'));
        $random_content = uniqid('hello-world1', true);
        file_put_contents($random_file_1, $random_content);
        $hash_1 = hash_file('sha256', $random_file_1);
        $random_file_2 = tempnam(sys_get_temp_dir(), uniqid('pvp2-'));
        file_put_contents($random_file_2, $random_content);
        $hash_2 = hash_file('sha256', $random_file_2);

        $file_1 = new File($hash_1, $random_file_1);
        $file_2 = new File($hash_2, $random_file_2);
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file_1);
        $file_repository->create($file_2);

        $file_recreated = $this->createMock(FileRecreated::class);
        $file_recreated->expects($this->once())
            ->method('process')
            ->with($random_file_2);
        $this->container->add(FileRecreated::class, $file_recreated);

        $scan_file_task = $this->container->get(ScanTask::class);
        $this->assertNull(
            $scan_file_task->run('id', json_encode(['filename' => $random_file_2]))
        );
        $this->assertSame($hash_1, $hash_2);
    }
}
