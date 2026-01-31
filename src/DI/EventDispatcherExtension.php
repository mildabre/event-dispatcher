<?php

declare(strict_types=1);

namespace Mildabre\EventDispatcher\DI;

use Mildabre\EventDispatcher\DomainEventListener;
use Mildabre\EventDispatcher\EventDispatcher;
use Nette\DI\CompilerExtension;
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
        $dispatcher = $builder->addDefinition($this->prefix('dispatcher'))
            ->setType(EventDispatcher::class);

        foreach ($builder->findByType(DomainEventListener::class) as $def) {
            $dispatcher->addSetup('addListener', [$def]);
        }
    }
}