<?php

declare(strict_types=1);

use Acme\Producer\Scan;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Spiral\Goridge\RPC\RPC;

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

// Create container and router
$container = new Container();
$container->delegate(new ReflectionContainer());

// Add implementations to container
$rpc = RPC::create('tcp://127.0.0.1:6001');
$container->add(RPC::class)->setConcrete($rpc)->setShared(true);
$container->add(Logger::class)->addArgument($rpc)->setShared(true);

$scan = $container->get(Scan::class);
$scan->run('/vault');
