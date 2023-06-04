<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use App\Exception\FilePathAlreadyExistsException;
use App\Model\File;
use InvalidArgumentException;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class FileRepository
{
    private PDO $pdo;

    public function __construct(
        private Database $database,
        private LoggerInterface $logger
    ) {
        $this->pdo = $database->getPdo();
    }

    /**
     * @deprecated Remove this
     */
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

    public function create(File $file): void
    {
        try {
            $query = "
                INSERT INTO files (hash, path, filename, filesize, mime, date_created, gps_lat, gps_lon, gps_alt, scanned_at, created_at, updated_at, removed_at)
                VALUES (:hash, :path, :filename, :filesize, :mime, :date_created, :gps_lat, :gps_lon, :gps_alt, :scanned_at, NOW(), NOW(), NULL)
            ";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':hash', $file->hash, PDO::PARAM_STR);
            $stmt->bindParam(':path', $file->path, PDO::PARAM_STR);
            $stmt->bindParam(':filename', $file->filename, PDO::PARAM_STR);
            $stmt->bindParam(':filesize', $file->filesize, PDO::PARAM_INT);
            $stmt->bindParam(':mime', $file->mime, PDO::PARAM_STR);
            $stmt->bindParam(':date_created', $file->date_created, PDO::PARAM_STR);
            $stmt->bindParam(':gps_lat', $file->gps_lat, PDO::PARAM_STR);
            $stmt->bindParam(':gps_lon', $file->gps_lon, PDO::PARAM_STR);
            $stmt->bindParam(':gps_alt', $file->gps_alt, PDO::PARAM_STR);
            $stmt->bindParam(':scanned_at', $file->scanned_at, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23505) { // unique_violation
                throw new FilePathAlreadyExistsException($file->path);
            }
            throw $e; // Other errors
        }
    }

    public function updateRemovedByPath(string $path, ?string $removed_at): void
    {
        $query = "
            UPDATE files
            SET removed_at = :removed_at
            WHERE path = :path
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':path', $path, PDO::PARAM_STR);
        $stmt->bindParam(':removed_at', $removed_at, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updateByPath(string $path, File $file): void
    {
        $query = "
            UPDATE files
            SET hash = :hash,
                path = :path,
                filename = :filename,
                filesize = :filesize,
                mime = :mime,
                date_created = :date_created,
                gps_lat = :gps_lat,
                gps_lon = :gps_lon,
                gps_alt = :gps_alt,
                scanned_at = :scanned_at,
                removed_at = :removed_at,
                updated_at = NOW()
            WHERE path = :path
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':path', $path, PDO::PARAM_STR);
        $stmt->bindParam(':hash', $file->hash, PDO::PARAM_STR);
        $stmt->bindParam(':filename', $file->filename, PDO::PARAM_STR);
        $stmt->bindParam(':filesize', $file->filesize, PDO::PARAM_INT);
        $stmt->bindParam(':mime', $file->mime, PDO::PARAM_STR);
        $stmt->bindParam(':date_created', $file->date_created, PDO::PARAM_STR);
        $stmt->bindParam(':gps_lat', $file->gps_lat, PDO::PARAM_STR);
        $stmt->bindParam(':gps_lon', $file->gps_lon, PDO::PARAM_STR);
        $stmt->bindParam(':gps_alt', $file->gps_alt, PDO::PARAM_STR);
        $stmt->bindParam(':scanned_at', $file->scanned_at, PDO::PARAM_STR);
        $stmt->bindParam(':removed_at', $removed_at, PDO::PARAM_STR);
        $stmt->execute();
    }



    public function findByHashOrPath(?string $hash, ?string $file_path): array
    {
        if ($hash === null && $file_path === null) {
            throw new InvalidArgumentException('Either hash or file path must be provided');
        }

        // Get the database rows that have the same hash or the same file path
        $query = "
            SELECT *
            FROM files
            WHERE hash = :hash OR path = :path";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
        $stmt->bindParam(':path', $file_path, PDO::PARAM_STR);
        $stmt->execute();

        // return $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $file = new File(
                $row['hash'],
                $row['path']
            );
            $file->filename = $row['filename'];
            $file->filesize = (int) $row['filesize'];
            $file->mime = $row['mime'];
            $file->date_created = $row['date_created'];
            $file->gps_lat = $row['gps_lat'];
            $file->gps_lon = $row['gps_lon'];
            $file->gps_alt = $row['gps_alt'];
            $file->scanned_at = $row['scanned_at'];
            $file->created_at = $row['created_at'];
            $file->updated_at = $row['updated_at'];
            $file->removed_at = $row['removed_at'];
            $results[] = $file;
        }

        return $results;
    }
}
