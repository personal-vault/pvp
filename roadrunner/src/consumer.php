<?php

declare(strict_types=1);

use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

$env = Environment::fromGlobals();

$isJobsMode = $env->getMode() === Mode::MODE_JOBS;

$consumer = new Consumer();

/** @var ReceivedTaskInterface $task */
while ($task = $consumer->waitTask()) {
    var_dump($task);

    $task->complete();
}
