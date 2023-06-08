<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use Test\TestCase;
use Test\Traits\HasJobsMock;

class FileMovedTest extends TestCase
{
    use HasJobsMock;

    public function testItSetsDatabaseRowToMoved(): void
    {
        // Create a file in the database only
        $path = tempnam(sys_get_temp_dir(), 'pvp-');
        file_put_contents($path, 'some content ' . uniqid('content-', true));
        $hash = hash_file('sha256', $path);
        $file = new File($hash, $path);
        /** @var FileRepository::class */
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        // Expect analyze job to be dispatched
        $this->injectQueueExpectation(1);

        $file_removed = $this->container->get(FileMoved::class);

        $new_path = $path . '.tgz';
        $this->assertNull(
            $file_removed->process($new_path, $hash)
        );

        // Check that the file was set to removed
        $file = $file_repository->findByPath($new_path);
        $this->assertNotNull($file);
    }

    public function testItThrowsIfHashIsNull(): void
    {
        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage('Hash must be set for FileMoved event');

        $file_removed = $this->container->get(FileMoved::class);
        $file_removed->process('/some/path', null);
    }
}
