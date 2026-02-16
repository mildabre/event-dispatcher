# Event Dispatcher

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://www.php.net/)

Simple, fresh, and modern event dispatcher with attribute-based event listener registration for Nette Framework applications.

## Features

- **Attribute-based registration** - Use PHP 8 attributes to mark events and listeners
-  **Simple API** - Minimal configuration, maximum productivity
-  **Modern PHP** - Requires PHP 8.3+ with full type safety
-  **Nette DI integration** - Seamless integration with Nette Dependency Injection
-  **Lightweight** - No bloat, just what you need
-  **Auto-discovery** - Automatic listener registration via service discovery

## Installation

Install via Composer:

```bash
composer require mildabre/event-dispatcher
```

## Configuration

Register the extension in your `common.neon`:

```neon
extensions:
    events: Mildabre\EventDispatcher\DI\EventDispatcherExtension
```

Optional dispatch disabling:

```neon
events:
    enabled: false       # default: true
```

## Define an Event

Create an event as value object (final readonly class) with class and mark it with the `#[Event]` attribute. 

```php
<?php

use Mildabre\EventDispatcher\Attributes\Event;

#[Event]
final readonly class UserRegistered
{
    public function __construct(
        public string $email,
        public \DateTimeImmutable $registeredAt,
    ) {}
}
```

## Create an Event Listener

Create a listener class with a `handle()` method that accepts your event:

```php
<?php

use Mildabre\ServiceDiscovery\Attributes\EventListener;

#[EventListener]
class SendWelcomeEmail
{
    public function __construct(
        private EmailService $emailService,
    ) {}

    public function handle(UserRegistered $event): void
    {
        $this->emailService->sendWelcome($event->email);
    }
}
```

## Dispatch Events

Inject the `EventDispatcher` and dispatch your events:

```php
<?php

use Mildabre\EventDispatcher\EventDispatcher;

class UserService
{
    public function __construct(
        private EventDispatcher $eventDispatcher,
    ) {}

    public function register(string $email, string $password): void
    {
        // ... registration logic ...
        
        $this->eventDispatcher->dispatch(
            new UserRegistered($email, new \DateTimeImmutable())
        );
    }
}
```

## How It Works

1. **Event Classes** must be annotated with `#[Event]` attribute
2. **Listener Classes** must be tagged with `#[EventListener]` attribute (from `mildabre/service-discovery`)
3. **Listener Methods** must be named `handle()` and accept exactly one parameter of the event class type
4. **Auto-registration** happens automatically via Nette DI - listeners are discovered and registered at compile time

##  Architecture

The event dispatcher follows a simple but powerful architecture:

```
Event → EventDispatcher → Listeners
  ↓            ↓              ↓
#[Event]   Auto-wired   #[EventListener]
              via           with handle()
           Nette DI           method
```

## Requirements

- PHP >= 8.1
- nette/di ^3.1
- nette/schema ^1.2
- mildabre/service-discovery ^0.1


## License

This project is licensed under the MIT License.


