<?php

declare(strict_types=1);

namespace App\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class NullLogger implements LoggerInterface
{
    use LoggerTrait;

    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
        assert(\is_scalar($level), 'Invalid log level type');
        assert(\is_string($message), 'Invalid log message type');
    }
}
