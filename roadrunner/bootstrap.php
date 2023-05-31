<?php
require_once(dirname(__FILE__) . '/vendor/autoload.php');

use League\Container\ReflectionContainer;
use App\Database;
use League\Route\Http\Exception\NotFoundException;
use Meorelia\Repository\File;
use Nyholm\Psr7;
use Nyholm\Psr7\Response;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use RoadRunner\Logger\Logger;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner;

// Create container and router
$container = new League\Container\Container();
$container->delegate(new ReflectionContainer());

$rpc = RPC::create('tcp://127.0.0.1:6001');
$container->add(Logger::class)->addArgument($rpc)->setShared(true);
