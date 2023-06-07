<?php declare(strict_types=1);

namespace App\Exception;

class ParseFileAttributesException extends \InvalidArgumentException
{
    public function __construct(string $path, string $message = '')
    {
        parent::__construct("Failed to parse {$path}: {$message}");
    }
}
