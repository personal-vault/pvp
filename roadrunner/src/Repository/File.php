<?php

declare(strict_types=1);

namespace Memorelia\Repository;

use Memorelia\Database;
use PDO;
use PDOException;
use RoadRunner\Logger\Logger;

class File
{
    private PDO $pdo;

    public function __construct(
        private Database $database,
        private Logger $logger
    ) {
        $this->pdo = $database->getPdo();
    }

    public function insertIfNotExist(string $hash, string $path, string $filename, int $filesize, string $mime): void
    {
        try {
            $this->pdo->beginTransaction();

            $query = "SELECT id FROM files WHERE hash = :hash";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->fetchColumn()) {
                echo "Row with hash {$hash} already exists.\n";
                $this->pdo->commit();
                return;
            }

            $query = "
                INSERT INTO files (hash, path, filename, filesize, mime, created_at, updated_at)
                VALUES (:hash, :path, :filename, :filesize, :mime, NOW(), NOW())
            ";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':path', $path, PDO::PARAM_STR);
            $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
            $stmt->bindParam(':filesize', $filesize, PDO::PARAM_INT);
            $stmt->bindParam(':mime', $mime, PDO::PARAM_STR);
            $stmt->execute();

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->logger->error('PDO Exception: ' . $e->getMessage());
        }
    }
}
