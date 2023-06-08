<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use App\Exception\FilePathAlreadyExistsException;
use App\Model\File;
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

    public function create(File $file): void
    {
        try {
            $query = "
                INSERT INTO files (hash, path, filename, filesize, mime, date_created, gps_lat, gps_lon, gps_alt, scanned_at, scan_version, created_at, updated_at, removed_at)
                VALUES (:hash, :path, :filename, :filesize, :mime, :date_created, :gps_lat, :gps_lon, :gps_alt, :scanned_at, :scan_version, NOW(), NOW(), NULL)
            ";
            $stmt = $this->pdo->prepare($query);
            // don't use `bindParam`
            // because https://www.php.net/manual/en/pdostatement.bindparam.php#94711
            $stmt->bindValue(':hash', $file->hash, PDO::PARAM_STR);
            $stmt->bindValue(':path', $file->path, PDO::PARAM_STR);
            $stmt->bindValue(':filename', $file->filename, PDO::PARAM_STR);
            $stmt->bindValue(':filesize', (int) $file->filesize, PDO::PARAM_INT);
            $stmt->bindValue(':mime', $file->mime, PDO::PARAM_STR);
            $stmt->bindValue(':date_created', $file->date_created, PDO::PARAM_STR);
            $stmt->bindValue(':gps_lat', $file->gps_lat, PDO::PARAM_STR);
            $stmt->bindValue(':gps_lon', $file->gps_lon, PDO::PARAM_STR);
            $stmt->bindValue(':gps_alt', $file->gps_alt, PDO::PARAM_STR);
            $stmt->bindValue(':scanned_at', $file->scanned_at, PDO::PARAM_STR);
            $stmt->bindValue(':scan_version', $file->scan_version, PDO::PARAM_INT);
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

    /**
     * @return File[]
     */
    public function findByHash(string $hash): array
    {
        $query = "
            SELECT *
            FROM files
            WHERE hash = :hash";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = self::makeFromRow($row);
        }

        return $results;
    }

    public function findByPath(string $path): ?File
    {
        $query = "
            SELECT *
            FROM files
            WHERE path = :path";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':path', $path, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return self::makeFromRow($row);
    }

    /**
     * @param string[] $row PDO row data from database
     */
    private static function makeFromRow(array $row): File
    {
        $file = new File(
            $row['hash'],
            $row['path']
        );
        $file->filename = $row['filename'];
        $file->filesize = (int) $row['filesize'];
        $file->mime = $row['mime'];
        $file->date_created = $row['date_created'];
        $file->gps_lat = (float)$row['gps_lat'];
        $file->gps_lon = (float)$row['gps_lon'];
        $file->gps_alt = (float)$row['gps_alt'];
        $file->scanned_at = $row['scanned_at'];
        $file->created_at = $row['created_at'];
        $file->updated_at = $row['updated_at'];
        $file->removed_at = $row['removed_at'];

        return $file;
    }
}
