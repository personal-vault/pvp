<?php declare(strict_types=1);

namespace App\Scan;

use InvalidArgumentException;

/**
 * Handles an event when a file is missing.
 */
interface ScanInterface
{
    public const VERSION = 1;

    public function process(string $path, ?string $hash = null): void;
}
