<?php

declare(strict_types=1);

namespace Mildabre\EventDispatcher;

interface DomainEventListener
{
    public function supports(object $event): bool;
    public function handle(object $event): void;
}