<?php declare(strict_types=1);

namespace App\Repository;

use App\Database;
use App\Exception\FilePathAlreadyExistsException;
use App\Model\File;
use App\Scan\MissingFile;
use InvalidArgumentException;
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
        $this->assertSame($file->filesize, $result['filesize']);
        $this->assertSame($file->mime, $result['mime']);

        $this->assertNull($result['scanned_at']);
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

    public function testItFindsByHashOrPathReturnsNoRowsIfNeitherIsFound(): void
    {
        $result = $this->file_repository->findByHashOrPath(uniqid(), uniqid());
        $this->assertSame([], $result);
    }

    public function testItFindsByHashOrPathReturnsModelsIfHashMatches(): void
    {
        $file = new File(uniqid('hash-'), uniqid('/file-'));
        $this->file_repository->create($file);

        $result = $this->file_repository->findByHashOrPath($file->hash, $file->path);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(File::class, $result[0]);
        $this->assertEquals($file->hash, $result[0]->hash);
        $this->assertEquals($file->path, $result[0]->path);
    }

    public function testFindByHashOrPathAcceptsNullHash(): void
    {
        $file = new File(uniqid('hash-'), uniqid('/file-'));
        $this->file_repository->create($file);

        $result = $this->file_repository->findByHashOrPath(null, $file->path);
        $this->assertCount(1, $result);
        $this->assertEquals($file->hash, $result[0]->hash);
        $this->assertEquals($file->path, $result[0]->path);
    }

    public function testFindByHashOrPathAcceptsNullPath(): void
    {
        $file = new File(uniqid('hash-'), uniqid('/file-'));
        $this->file_repository->create($file);

        $result = $this->file_repository->findByHashOrPath($file->hash, null);
        $this->assertCount(1, $result);
        $this->assertEquals($file->hash, $result[0]->hash);
        $this->assertEquals($file->path, $result[0]->path);
    }

    public function testFindByHashOrPathDoesNotAcceptBothPathAndHashNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->file_repository->findByHashOrPath(null, null);
    }
}
