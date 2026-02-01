<?php

declare(strict_types=1);

namespace Mildabre\EventDispatcher\DI;

use Mildabre\EventDispatcher\EventDispatcher;
use Mildabre\ServiceDiscovery\DI\ServiceDiscoveryExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class EventDispatcherExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'enabled' => Expect::bool()->default(true),
        ]);
    }

    public function loadConfiguration(): void
    {
        if (!$this->config->enabled) {
            return;
        }

        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('dispatcher'))
            ->setType(EventDispatcher::class);

    }

    public function beforeCompile(): void
    {
        if (!$this->config->enabled) {
            return;
        }

        $builder = $this->getContainerBuilder();
        $dispatcher = $builder->getDefinition($this->prefix('dispatcher'));

        $listeners = array_filter($builder->getDefinitions(), fn(Definition $def) => (bool) $def->getTag(ServiceDiscoveryExtension::TagEventListener));
        foreach ($listeners as $listener) {
            $dispatcher->addSetup('addListener', [$listener]);
        }
    }
}