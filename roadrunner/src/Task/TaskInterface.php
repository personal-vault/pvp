<?php

declare(strict_types=1);

namespace App\Task;

use Psr\Container\ContainerInterface;

interface TaskInterface {
    public function container(ContainerInterface $container): void;
    public function run(string $id, string $payload): void;
}
