<?php

declare(strict_types=1);

namespace App\Task;

interface TaskInterface {
    public function run(string $id, string $payload): void;
}
