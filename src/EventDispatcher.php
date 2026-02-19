<?php

declare(strict_types=1);

namespace Mildabre\EventDispatcher;

use RuntimeException;
use Throwable;

class EventDispatcher
{
    /**
     * @var array<string, list<array{object: object, class: class-string}>>
     */
    private array $listeners = [];

    /**
     * @internal
     */
    public function addListener(object $listenerOrProxy, string $eventClass, string $listenerClass): void
    {
        $this->listeners[$eventClass][] = ['object' => $listenerOrProxy, 'class' => $listenerClass];
    }

    public function dispatch(object $event): void
    {
        $listeners = $this->listeners[$event::class] ?? [];

        if (!$listeners) {
            return;
        }

        foreach ($listeners as $listenerData) {
            try {
                $object = $listenerData['object'];
                $listener = $object instanceof ListenerProxy ? $object->get() :  $object;
                $listener->handle($event);

            } catch (Throwable $exception) {
                throw new RuntimeException(
                    sprintf("Listener '%s' failed handling event '%s': %s", $listenerData['class'], $event::class, $exception->getMessage()),
                    previous: $exception
                );
            }
        }
    }
}