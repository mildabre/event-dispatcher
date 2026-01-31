<?php

declare(strict_types=1);

namespace Mildabre\EventDispatcher;

class EventDispatcher
{
    /**
     * @var list<DomainEventListener>
     */
    private array $listeners = [];

    public function addListener(DomainEventListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function dispatch(object $event): void
    {
        foreach ($this->listeners as $listener) {
            if ($listener->supports($event)) {
                $listener->handle($event);
            }
        }
    }
}