<?php

declare(strict_types=1);

namespace App\Parser;

use App\Exception\FilePathNotFoundException;
use App\Exception\ParseFileAttributesException;
use App\Model\File;
use App\Repository\FileIssueRepository;
use InvalidArgumentException;

/**
 * Parser class
 *
 * Parses a file and returns file information, such as mime type, creation date,
 * gps coordinates, etc.
 *
 * These are used to populate the database.
 */
class Parser
{
    public function __construct(
        private FileIssueRepository $file_issue_repository
    ) {}

    public function parse(
        string $path,
        string $hash
    ): File {
        if (!file_exists($path)) {
            throw new FilePathNotFoundException($path);
        }

        $attributes = $this->extract_attributes($path);

        $file = new File($hash, $path);
        $file->filename = $attributes[0]->FileName ?? null;
        $file->filesize = filesize($path);

        if (isset($attributes[0]->Error)) {
            // Failed to extract information from file
            $this->file_issue_repository->create($file, 'Parser/exiftool', $attributes[0]->Error);
            return $file;
        }

        if (!empty($attributes[0]->CreateDate) && $attributes[0]->CreateDate !== '0000:00:00 00:00:00') {
            $file->date_created = self::convertDateToIso8601($attributes[0]->CreateDate) ?? null;
        }
        if (!empty($attributes[0]->GPSPosition)) {
            [$lat, $lon] = self::parseDMS($attributes[0]->GPSPosition);
            $file->gps_lat = $lat;
            $file->gps_lon = $lon;
        }
        if (!empty($attributes[0]->GPSAltitude)) {
            $file->gps_alt = (float) $attributes[0]->GPSAltitude;
        }
        $file->mime = $attributes[0]->MIMEType ?? null;
        return $file;
    }

    private function extract_attributes($path): array
    {
        $output = [];
        $exit_code = null;
        exec('exiftool -j ' . escapeshellarg($path), $output, $exit_code);
        $result = json_decode(implode("\n", $output));
        return $result;
    }

    private static function convertDMStoDEC($deg, $min, $sec, $hem) {
        $d = floatval($deg) + floatval($min)/60 + floatval($sec)/3600;
        return ($hem=='S' || $hem=='W') ? $d*=-1 : $d;
    }

    private static function parseDMS($input) {
        preg_match('/(\d+) deg (\d+)\' (\d+\.\d+)" (\w), (\d+) deg (\d+)\' (\d+\.\d+)" (\w)/', $input, $matches);

        $lat = self::convertDMStoDEC($matches[1], $matches[2], $matches[3], $matches[4]);
        $lon = self::convertDMStoDEC($matches[5], $matches[6], $matches[7], $matches[8]);

        return [$lat, $lon];
    }

    private static function convertDateToIso8601($date) {
        $newDate = str_replace(':', '-', substr($date, 0, 10)) . substr($date, 10);
        return $newDate;
    }
}
