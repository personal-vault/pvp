<?php declare(strict_types=1);

namespace App\Scan;

use App\Model\File;
use App\Repository\FileRepository;
use Test\TestCase;
use Test\Traits\HasJobsMock;

class FileCreatedTest extends TestCase
{
    use HasJobsMock;

    public function testItCreatesADatabaseRow(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pvp-');
        file_put_contents($path, uniqid('file-content-'));
        $hash = hash_file('sha256', $path);
        /** @var FileRepository::class */
        $file_repository = $this->container->get(FileRepository::class);
        // Expect analyze job to be dispatched
        $this->injectQueueExpectation(1);

        $file_created = $this->container->get(FileCreated::class);

        $this->assertNull(
            $file_created->process($path, $hash)
        );

        // Check that the file exists in the DB
        $files = $file_repository->findByHashOrPath(null, $path);
        $this->assertCount(1, $files);
        $this->assertFalse($files[0]->isRemoved());
    }

    public function testItSkipsCreatingRowIfAlreadyExists(): void
    {
        // Create a file in the database only
        $path = tempnam(sys_get_temp_dir(), 'pvp-');
        $file = new File(uniqid('hash'), $path);
        /** @var FileRepository::class */
        $file_repository = $this->container->get(FileRepository::class);
        $file_repository->create($file);

        // Expect analyze job NOT to be dispatched
        $this->injectQueueUnexpectation();

        $file_created = $this->container->get(FileCreated::class);

        $this->assertNull(
            $file_created->process($path, uniqid('hash-'))
        );

        // Check that the file exists in the DB
        $files = $file_repository->findByHashOrPath(null, $path);
        $this->assertCount(1, $files);
        $this->assertFalse($files[0]->isRemoved());
    }
}
