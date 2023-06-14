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

    public function create(File $file): int
    {
        try {
            $query = "
                INSERT INTO files (hash, path, filename, filesize, mime, date_created, gps_lat, gps_lon, gps_alt, metadata, transcript, scanned_at, analyzed_at, scan_version, created_at, updated_at, removed_at)
                VALUES (:hash, :path, :filename, :filesize, :mime, :date_created, :gps_lat, :gps_lon, :gps_alt, :metadata, :transcript, :scanned_at, :analyzed_at, :scan_version, NOW(), NOW(), NULL)
            ";
            $stmt = $this->pdo->prepare($query);
            // don't use `bindValue`
            // because https://www.php.net/manual/en/pdostatement.bindparam.php#94711
            $stmt->bindValue(':hash', $file->hash, PDO::PARAM_STR);
            $stmt->bindValue(':path', $file->path, PDO::PARAM_STR);
            $stmt->bindValue(':filename', $file->filename, PDO::PARAM_STR);
            $stmt->bindValue(':filesize', $file->filesize ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':mime', $file->mime, PDO::PARAM_STR);
            $stmt->bindValue(':date_created', $file->date_created, PDO::PARAM_STR);
            $stmt->bindValue(':gps_lat', $file->gps_lat, PDO::PARAM_STR);
            $stmt->bindValue(':gps_lon', $file->gps_lon, PDO::PARAM_STR);
            $stmt->bindValue(':gps_alt', $file->gps_alt, PDO::PARAM_STR);
            $stmt->bindValue(':metadata', isset($file->metadata) ? json_encode($file->metadata) : null, PDO::PARAM_STR);
            $stmt->bindValue(':transcript', $file->transcript, PDO::PARAM_STR);
            $stmt->bindValue(':scanned_at', $file->scanned_at, PDO::PARAM_STR);
            $stmt->bindValue(':analyzed_at', $file->analyzed_at, PDO::PARAM_STR);
            $stmt->bindValue(':scan_version', $file->scan_version, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23505) { // unique_violation
                throw new FilePathAlreadyExistsException($file->path);
            }
            throw $e; // Other errors
        }
        $last_insert_id = (int) $this->pdo->lastInsertId();
        $file->id = $last_insert_id;
        return $file->id;
    }

    public function updateRemovedByPath(string $path, ?string $removed_at): void
    {
        $query = "
            UPDATE files
            SET removed_at = :removed_at
            WHERE path = :path
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':path', $path, PDO::PARAM_STR);
        $stmt->bindValue(':removed_at', $removed_at, PDO::PARAM_STR);
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
        $stmt->bindValue(':path', $path, PDO::PARAM_STR);
        $stmt->bindValue(':hash', $file->hash, PDO::PARAM_STR);
        $stmt->bindValue(':filename', $file->filename, PDO::PARAM_STR);
        $stmt->bindValue(':filesize', $file->filesize, PDO::PARAM_INT);
        $stmt->bindValue(':mime', $file->mime, PDO::PARAM_STR);
        $stmt->bindValue(':date_created', $file->date_created, PDO::PARAM_STR);
        $stmt->bindValue(':gps_lat', $file->gps_lat, PDO::PARAM_STR);
        $stmt->bindValue(':gps_lon', $file->gps_lon, PDO::PARAM_STR);
        $stmt->bindValue(':gps_alt', $file->gps_alt, PDO::PARAM_STR);
        $stmt->bindValue(':scanned_at', $file->scanned_at, PDO::PARAM_STR);
        $stmt->bindValue(':removed_at', $file->removed_at, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updateById(int $id, File $file): void
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
                transcript = :transcript,
                scanned_at = :scanned_at,
                removed_at = :removed_at,
                updated_at = NOW()
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':path', $file->path, PDO::PARAM_STR);
        $stmt->bindValue(':hash', $file->hash, PDO::PARAM_STR);
        $stmt->bindValue(':filename', $file->filename, PDO::PARAM_STR);
        $stmt->bindValue(':filesize', $file->filesize, PDO::PARAM_INT);
        $stmt->bindValue(':mime', $file->mime, PDO::PARAM_STR);
        $stmt->bindValue(':date_created', $file->date_created, PDO::PARAM_STR);
        $stmt->bindValue(':gps_lat', $file->gps_lat, PDO::PARAM_STR);
        $stmt->bindValue(':gps_lon', $file->gps_lon, PDO::PARAM_STR);
        $stmt->bindValue(':gps_alt', $file->gps_alt, PDO::PARAM_STR);
        $stmt->bindValue(':transcript', $file->transcript, PDO::PARAM_STR);
        $stmt->bindValue(':scanned_at', $file->scanned_at, PDO::PARAM_STR);
        $stmt->bindValue(':removed_at', $file->removed_at, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function findById(int $id): ?File
    {
        $query = "
            SELECT *
            FROM files
            WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === null) {
            return null;
        }

        return self::makeFromRow($row);
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
        $stmt->bindValue(':hash', $hash, PDO::PARAM_STR);
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
        $stmt->bindValue(':path', $path, PDO::PARAM_STR);
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
        $file->id = (int) $row['id'];
        $file->filename = $row['filename'];
        $file->filesize = (int) $row['filesize'];
        $file->mime = $row['mime'];
        $file->date_created = $row['date_created'];
        $file->gps_lat = $row['gps_lat'] ? (float)$row['gps_lat'] : null;
        $file->gps_lon = $row['gps_lon'] ? (float)$row['gps_lon'] : null;
        $file->gps_alt = $row['gps_alt'] ? (float)$row['gps_alt'] : null;
        $file->scanned_at = $row['scanned_at'];
        $file->scan_version = $row['scan_version'];
        $file->created_at = $row['created_at'];
        $file->updated_at = $row['updated_at'];
        $file->removed_at = $row['removed_at'];

        return $file;
    }
}
