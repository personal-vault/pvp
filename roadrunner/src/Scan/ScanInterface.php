<?php declare(strict_types=1);

namespace App\Scan;

use InvalidArgumentException;

/**
 * Handles an event when a file is missing.
 */
interface ScanInterface
{
    public function process(string $file_path): void;
}
