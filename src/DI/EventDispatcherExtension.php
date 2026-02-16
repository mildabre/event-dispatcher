<?php

declare(strict_types=1);

namespace Mildabre\EventDispatcher\DI;

use LogicException;
use Mildabre\EventDispatcher\Attributes\Event;
use Mildabre\EventDispatcher\EventDispatcher;
use Mildabre\EventDispatcher\ListenerProxy;
use Mildabre\ServiceDiscovery\Attributes\EventListener;
use Mildabre\ServiceDiscovery\DI\ServiceDiscoveryExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use ReflectionClass;
use ReflectionNamedType;

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

        $listenerDefs = array_filter(
            $builder->getDefinitions(),
            fn(Definition $def) => (bool) $def->getTag(ServiceDiscoveryExtension::TagEventListener)
        );

        foreach ($listenerDefs as $listenerName => $listenerDef) {
            $listenerClass = $listenerDef->getType();

            if (!$listenerClass) {
                throw new LogicException(sprintf("Listener '%s' must have a type defined.", $listenerName));
            }

            $eventClass = $this->validateAndExtractEventClass($listenerClass);

            $proxyName = $this->prefix('proxy.' . $listenerName);

            $builder->addDefinition($proxyName)
                ->setType(ListenerProxy::class)
                ->setArguments(['serviceName' => $listenerName]);

            $dispatcher->addSetup('addListener', ['@' . $proxyName, $eventClass, $listenerClass]);
        }
    }

    private function validateAndExtractEventClass(string $class): string
    {
        if (!class_exists($class)) {
            throw new LogicException($class . ", listener class does not exist.");
        }

        $rc = new ReflectionClass($class);

        if (!$rc->getAttributes(EventListener::class)) {
            throw new LogicException($class . ", listener must be annotated with #[EventListener] attribute.");
        }

        if (!$rc->hasMethod('handle')) {
            throw new LogicException($class . ", listener must implement method handle().");
        }

        $method = $rc->getMethod('handle');
        if (!$method->isPublic()) {
            throw new LogicException($class . ", listener method handle() must be public.");
        }

        $parameters = $method->getParameters();

        if (count($parameters) !== 1) {
            throw new LogicException($class . ", listener method handle() must have exactly one parameter, " . count($parameters) . " given.");
        }

        $parameter = $parameters[0];
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new LogicException($class . ", listener method handle() parameter must be a class type (not builtin or union type).");
        }

        $eventClass = $type->getName();

        if (!class_exists($eventClass)) {
            throw new LogicException($class . ", listener event class '$eventClass' does not exist.");
        }

        $eventRc = new ReflectionClass($eventClass);
        if (!$eventRc->getAttributes(Event::class)) {
            throw new LogicException($class . ", listener event class '$eventClass' must be annotated with #[Event] attribute.");
        }

        return $eventClass;
    }
}