<?php declare(strict_types=1);

namespace App\Repository;

use App\Database;
use App\Exception\FilePathAlreadyExistsException;
use App\Model\File;
use PDO;
use Test\TestCase;

final class FileRepositoryTest extends TestCase
{
    private FileRepository $file_repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->file_repository = $this->container->get(FileRepository::class);
    }

    public function testItCreatesAFileRow(): void
    {
        $file = new File(uniqid('hash-'), uniqid('/file-'));
        $file->filename = uniqid('filename-');
        $file->filesize = (int)rand(1, 1000);
        $file->mime = uniqid('mime-');
        $file->scanned_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $file->created_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $file->updated_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $file->removed_at = null;
        $file->scan_version = rand(1, 1000);
        $this->file_repository->create($file);

        $database = $this->container->get(Database::class);
        $pdo = $database->getPdo();
        $query = "SELECT * FROM files WHERE hash = :hash";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':hash', $file->hash, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($result['id']);
        $this->assertSame($file->hash, $result['hash']);
        $this->assertSame($file->path, $result['path']);
        $this->assertSame($file->filename, $result['filename']);
        $this->assertSame($file->filesize, (int) $result['filesize']);
        $this->assertSame($file->mime, $result['mime']);
        $this->assertSame($file->scan_version, (int) $result['scan_version']);

        $this->assertNotEmpty($result['scanned_at']);
        $this->assertNotEmpty($result['created_at']);
        $this->assertNotEmpty($result['updated_at']);
        $this->assertNull($result['removed_at']);
    }

    public function testCreateThrowsIfPathIsNotUnique(): void
    {
        $this->expectException(FilePathAlreadyExistsException::class);

        $path = uniqid('/file-');
        $this->file_repository->create(new File(uniqid('hash1-'), $path));
        $this->file_repository->create(new File(uniqid('hash2-'), $path));
    }

    public function testItFindsByHashReturnsNoRowsIfNotIsFound(): void
    {
        $result = $this->file_repository->findByHash(uniqid());
        $this->assertSame([], $result);
    }

    public function testItFindsByPathReturnsNoRowsIfNotIsFound(): void
    {
        $result = $this->file_repository->findByPath(uniqid());
        $this->assertNull($result);
    }

    public function testItFindsByPathReturnsModelsIfPathMatches(): void
    {
        $file = new File(uniqid('hash-'), uniqid('/file-'));
        $this->file_repository->create($file);

        $found_file = $this->file_repository->findByPath($file->path);
        $this->assertInstanceOf(File::class, $found_file);
        $this->assertEquals($file->hash, $found_file->hash);
        $this->assertEquals($file->path, $found_file->path);
    }

    public function testItFindsByHashReturnsModelsIfHashMatches(): void
    {
        $file = new File(uniqid('hash-'), uniqid('/file-'));
        $this->file_repository->create($file);

        $result = $this->file_repository->findByHash($file->hash);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(File::class, $result[0]);
        $this->assertEquals($file->hash, $result[0]->hash);
        $this->assertEquals($file->path, $result[0]->path);
    }
}
