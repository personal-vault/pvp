<?php declare(strict_types=1);

namespace Test;

use App\ServiceProvider;
use League\Container\Container;
use League\Container\ReflectionContainer;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    protected Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer());
        $this->container->addServiceProvider(new ServiceProvider);
    }
}
