<?php

declare(strict_types=1);

namespace Mildabre\EventDispatcher;

use LogicException;
use Mildabre\EventDispatcher\Attributes\Event;
use ReflectionClass;
use ReflectionNamedType;

class EventDispatcher
{
    /**
     * @var array<string, list<object>>
     */
    private array $listeners = [];

    public function addListener(object $listener): void
    {
        $rc = new ReflectionClass($listener);

        if (!$rc->hasMethod('handle')) {
            throw new LogicException("$rc->name must implement method handle().");
        }

        $method = $rc->getMethod('handle');
        $parameters = $method->getParameters();
        $parameter = $parameters[0] ?? null;
        $type = $parameter?->getType();

        if (count($parameters) !== 1 || !$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new LogicException(sprintf("%d, method handle must have exactly one parameter of class type.", $rc->name));
        }

        $eventClass = $type->getName();
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(object $event): void
    {
        $rc = new ReflectionClass($event);
        if (!$rc->getAttributes(Event::class)) {
            throw new LogicException(sprintf("Event class %s must be annotated with attribute '#[Event]'.", $event::class));
        }

        $listeners = $this->listeners[$event::class] ?? [];
        foreach ($listeners as $listener) {
            $listener->handle($event);
        }
    }
}