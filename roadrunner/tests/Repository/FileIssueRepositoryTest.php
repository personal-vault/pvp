<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use App\Model\File;
use PDO;
use Test\TestCase;

final class FileIssueRepositoryTest extends TestCase
{
    private FileIssueRepository $file_issue_repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->file_issue_repository = $this->container->get(FileIssueRepository::class);
    }

    public function testItCreatesAFileIssueEntry(): void
    {
        $file = new File(uniqid('hash-'), uniqid('/file-'));
        $file->name = uniqid('name-');
        $file->size = (int)rand(1, 1000);
        $this->file_issue_repository->create($file, 'some/tool', 'Some issue');

        $database = $this->container->get(Database::class);
        $pdo = $database->getPdo();
        $query = "SELECT * FROM file_issues WHERE hash = :hash";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':hash', $file->hash, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($result['id']);
        $this->assertSame($file->hash, $result['hash']);
        $this->assertSame($file->path, $result['path']);
        $this->assertSame($file->name, $result['name']);
        $this->assertSame($file->size, (int) $result['size']);
        $this->assertSame('some/tool', $result['tool']);
        $this->assertSame('Some issue', $result['issue']);
        $this->assertNotEmpty($result['created_at']);
    }
}
