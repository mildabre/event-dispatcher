# Examples

## Multiple Listeners for One Event

```php
#[Event]
class OrderPlaced
{
    public function __construct(
        public readonly int $orderId,
        public readonly float $amount,
    ) {}
}

#[EventListener]
class SendOrderConfirmation
{
    public function handle(OrderPlaced $event): void
    {
        // Send confirmation email
    }
}

#[EventListener]
class UpdateInventory
{
    public function handle(OrderPlaced $event): void
    {
        // Update stock levels
    }
}

#[EventListener]
class NotifyWarehouse
{
    public function handle(OrderPlaced $event): void
    {
        // Notify warehouse system
    }
}
```

All three listeners will be automatically called when `OrderPlaced` event is dispatched.

## Dependency Injection in Listeners

```php
#[EventListener]
class LogUserActivity
{
    public function __construct(
        private LoggerInterface $logger,
        private Database $database,
        private CacheStorage $cache,
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $this->logger->info('User logged in', ['user_id' => $event->userId]);
        $this->database->query('UPDATE users SET last_login = NOW() WHERE id = ?', $event->userId);
        $this->cache->remove('user-' . $event->userId);
    }
}
```
