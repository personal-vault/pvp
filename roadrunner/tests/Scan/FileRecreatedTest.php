<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use Test\TestCase;
use Test\Traits\HasJobsMock;

class FileRecreatedTest extends TestCase
{
    use HasJobsMock;

    public function testItSetsDatabaseRowToNonRemovedFile(): void
    {
        // Create a file in the database only
        $path = tempnam(sys_get_temp_dir(), 'pvp-');
        file_put_contents($path, uniqid('file-contents-', true));
        $hash = hash_file('sha256', $path);
        $file = new File($hash, $path);
        $file->removed_at = date('Y-m-d H:i:s');

        /** @var FileRepository::class */
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        // Expect analyze job to be dispatched
        $this->injectQueueExpectation(1);

        $file_removed = $this->container->get(FileRecreated::class);

        $this->assertNull(
            $file_removed->process($path, $hash)
        );

        // Check that the file was set to removed
        $files = $file_repository->findByHashOrPath(null, $path);
        $this->assertCount(1, $files);
        $this->assertFalse($files[0]->isRemoved());
    }
}
