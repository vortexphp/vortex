# Events

**`Vortex\Events\Dispatcher`** is a synchronous bus: **`dispatch($event)`** invokes listeners in registration order. It is a **singleton** from **`bootstrap/app.php`**. **`Vortex\Events\EventBus`** is the static entry (**`EventBus::dispatch($event)`**) — same instance as injecting **`Dispatcher`**.

## Registration

**`config/events.php`** → **`Repository::get('events.listen')`** — map event class names to one or more listener class names:

```php
<?php

declare(strict_types=1);

use App\Events\UserRegistered;
use App\Listeners\SendWelcomeEmail;

return [
    'listen' => [
        UserRegistered::class => [SendWelcomeEmail::class],
    ],
];
```

At runtime you can also call **`$dispatcher->listen(Event::class, $callableOrClass)`** (e.g. from **`bootstrap/app.php`** after **`make(Dispatcher::class)`** — resolve the singleton and call **`listen`**, or register a small bootstrap closure).

## Listeners

Each listener is resolved with **`Container::make`**. Implement either:

- **`public function handle(YourEvent $event): void`**, or  
- **`public function __invoke(YourEvent $event): void`**

Closures are allowed when registered via **`listen()`** in PHP code.

## Dispatching

```php
use App\Events\UserRegistered;
use Vortex\Events\EventBus;

EventBus::dispatch(new UserRegistered($user));
```

Events are plain **objects** (typically small **`final` readonly** DTOs under **`app/Events/`**). Work stays synchronous; use a queue later for slow side effects.

## Related

- [Bootstrap and container](bootstrap-and-container.md)
- [Configuration](configuration.md)
- [Events](../developer/events.md)
