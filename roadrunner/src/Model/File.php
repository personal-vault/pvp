<?php declare(strict_types=1);

namespace App\Model;

class File
{
    public int $id;
    public string $hash;
    public string $path;
    public ?string $filename = null;
    public ?int $filesize = null;
    public ?string $mime = null;
    public ?string $date_created = null;
    public ?string $gps_lat = null;
    public ?string $gps_lon = null;
    public ?string $gps_alt = null;
    public ?string $scan_version = null;
    public ?string $scanned_at = null;
    public string $created_at;
    public string $updated_at;
    public ?string $removed_at = null;

    public function __construct(string $hash, string $path) {
        $this->hash = $hash;
        $this->path = $path;
    }
}
