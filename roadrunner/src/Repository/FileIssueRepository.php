<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use App\Exception\FilePathAlreadyExistsException;
use App\Model\File;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class FileIssueRepository
{
    private PDO $pdo;

    public function __construct(
        private Database $database,
        private LoggerInterface $logger
    ) {
        $this->pdo = $database->getPdo();
    }

    public function create(File $file, string $tool, string $issue): void
    {
        try {
            $query = "
                INSERT INTO file_issues (hash, path, filename, filesize, tool, issue, created_at)
                VALUES (:hash, :path, :filename, :filesize, :tool, :issue, NOW())
            ";
            $stmt = $this->pdo->prepare($query);
            // don't use `bindParam`
            // because https://www.php.net/manual/en/pdostatement.bindparam.php#94711
            $stmt->bindValue(':hash', $file->hash, PDO::PARAM_STR);
            $stmt->bindValue(':path', $file->path, PDO::PARAM_STR);
            $stmt->bindValue(':filename', $file->filename, PDO::PARAM_STR);
            $stmt->bindValue(':filesize', (int) $file->filesize, PDO::PARAM_INT);
            $stmt->bindValue(':tool', $tool, PDO::PARAM_STR);
            $stmt->bindValue(':issue', $issue, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            // Pay if forward
            throw $e;
        }
    }
}
