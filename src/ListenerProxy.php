<?php

declare(strict_types=1);

namespace Mildabre\EventDispatcher;

use Nette\DI\Container;

final class ListenerProxy
{
    private ?object $instance = null;

    public function __construct(
        private readonly Container $container,
        private readonly string $serviceName,
    ) {}

    public function get(): object
    {
        return $this->instance ??= $this->container->getService($this->serviceName);
    }
}