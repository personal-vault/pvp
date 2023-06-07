<?php declare(strict_types=1);

namespace App\Exception;

class FilePathNotFoundException extends \InvalidArgumentException
{
    public function __construct(string $path)
    {
        parent::__construct("File path {$path} not found");
    }
}
