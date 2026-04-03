# Events

The engine API is in [Framework: Events](../framework/events.md). Use events to **react** to something that already happened (user signed up, post published) without growing the main handler.

## Shape

1. **`app/Events/YourThing.php`** — small immutable object holding what listeners need.
2. **`app/Listeners/DoSomething.php`** — **`handle(YourThing $event): void`** (or **`__invoke`**).
3. **`config/events.php`** — under **`listen`**, map **`YourThing::class`** → listener class(es).
4. From a handler: **`EventBus::dispatch(new YourThing(...))`** after the core DB/session work succeeds.

> **Example — after registration (conceptual)**

```php
use App\Events\UserRegistered;
use App\Models\User; // your domain model
use Vortex\Events\EventBus;

// User::create(...) succeeded; $user is the model instance
EventBus::dispatch(new UserRegistered($user));
```

```php
// app/Events/UserRegistered.php (import App\Models\User)
final class UserRegistered
{
    public function __construct(
        public readonly User $user,
    ) {
    }
}
```

```php
// app/Listeners/LogNewUser.php
final class LogNewUser
{
    public function handle(UserRegistered $event): void
    {
        // log, analytics, optional second DB table — keep fast or move to a queue later
    }
}
```

Keep the **HTTP handler** focused on validation, **`User::create`**, redirect; listeners handle follow-ups.

## Related

- [Handlers](handlers.md) — **`EventBus`** alongside **`DB`** / **`Cache`**
- [Framework: Events](../framework/events.md)
