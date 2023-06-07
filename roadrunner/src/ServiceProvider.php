<?php declare(strict_types=1);

namespace App;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\Log\LoggerInterface;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Logger;

/**
 * @link https://container.thephpleague.com/4.x/service-providers/
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * The provides method is a way to let the container
     * know that a service is provided by this service
     * provider. Every service that is registered via
     * this service provider must have an alias added
     * to this array or it will be ignored.
     */
    public function provides(string $id): bool
    {
        $services = [
            JobsInterface::class,
            Logger::class,
            LoggerInterface::class,
            RPC::class,
        ];

        return in_array($id, $services);
    }

    /**
     * The register method is where you define services
     * in the same way you would directly with the container.
     * A convenience getter for the container is provided, you
     * can invoke any of the methods you would when defining
     * services directly, but remember, any alias added to the
     * container here, when passed to the `provides` nethod
     * must return true, or it will be ignored by the container.
     */
    public function register(): void
    {
        $rpc = RPC::create('tcp://127.0.0.1:6001');
        // $this->getContainer()->add(RPC::class, $rpc);

        $this->getContainer()->add(Logger::class)
            ->addArgument($rpc)
            ->setShared(true);

        $logger = new Logger($rpc);
        $this->getContainer()->add(LoggerInterface::class, $logger)->setShared(true);

        $this->getContainer()->add(JobsInterface::class, new Jobs($rpc));
    }
}
