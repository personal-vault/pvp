<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use Test\TestCase;
use Test\Traits\HasJobsMock;

class FileRemovedTest extends TestCase
{
    use HasJobsMock;

    public function testItSetsDatabaseRowToRemoved(): void
    {
        // Create a file in the database only
        $path = tempnam(sys_get_temp_dir(), 'pvp-');
        $file = new File(uniqid('hash'), $path);
        /** @var FileRepository::class */
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        // Expect analyze job to be dispatched
        $this->injectQueueExpectation(1);

        $file_removed = $this->container->get(FileRemoved::class);

        $this->assertNull(
            $file_removed->process($path)
        );

        // Check that the file was set to removed
        $files = $file_repository->findByHashOrPath(null, $path);
        $this->assertCount(1, $files);
        $this->assertTrue($files[0]->isRemoved());
    }
}
