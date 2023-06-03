<?php declare(strict_types=1);

namespace App\Scan;

use InvalidArgumentException;

/**
 * Handles an event when a file is missing.
 */
class FileRemoved implements ScanInterface
{
    public function process(string $path): void
    {
        // Update DB and set path to removed

        // Dispatch analyze job
    }
}
