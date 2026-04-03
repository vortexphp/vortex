# Bootstrap and container

## Entry points

- **HTTP**: `public/index.php` loads `bootstrap/app.php`, builds `Kernel`, calls `send()`.
- **CLI**: `power` runs `ConsoleApplication::boot($basePath)->run($argv)` (no full HTTP bootstrap unless a command loads it, e.g. `migrate`).

## `bootstrap/app.php`

Typical responsibilities:

1. Load Composer autoload and **`.env`** via `Env::load()`.
2. Create **`Container`**, register itself: `instance(Container::class, $container)`.
3. Register **singletons**: `Repository`, `Connection`, `Cache`, `Dispatcher`, `Mailer`, `Session`, `Csrf`, `LocalPublicStorage`, `Translator`, Twig `Factory`, `Router`, `ErrorRenderer`, and any app classes (e.g. docs services).
4. Call **`Repository::setInstance`**, **`Session::setInstance`**, **`Csrf::setInstance`**, **`LocalPublicStorage::setInstance`**, **`Translator::setInstance`**, **`View::useFactory(...)`**, **`AppContext::set($container)`**.

`bootstrap/app.php` **returns** the `Container` instance.

> **Example — register a singleton your handlers need**

```php
use Vortex\Config\Repository;
use Vortex\Container;

$container->singleton(App\Services\BillingClient::class, static function (Container $c): App\Services\BillingClient {
    return new App\Services\BillingClient(
        (string) Repository::get('billing.api_key', ''),
    );
});
```

Add a `billing` array (or your own keys) under **`config/`** and read it with **`Repository::get`** as above.

## Container API

- **`$container->singleton(Abstract::class, closure)`** — one shared instance; the closure receives `Container $c`.
- **`$container->instance(Abstract::class, $object)`** — bind a concrete instance (e.g. the container itself).
- **`$container->make(ClassName::class)`** — resolve: use binding, or `new` with constructor injection by type.

Unresolvable constructor parameters (no type, or unknown class) throw.

## `AppContext`

After bootstrap, **`AppContext::container()`** returns the same container from anywhere (e.g. rare cases outside DI). Prefer constructor injection in handlers and middleware. **`Vortex\Database\DB`** forwards to the singleton **`Connection`** (e.g. **`DB::transaction`**) — see [developer/database.md](../developer/database.md). **`Vortex\Cache\Cache`** forwards to **`Contracts\Cache`** (e.g. **`Cache::remember`**) — see [Cache](cache.md). **`Vortex\Events\EventBus`** forwards to **`Dispatcher`** — see [Events](events.md). **`Vortex\Mail\Mail`** forwards to **`Mailer`** — see [Mail](mail.md).

> **Example — resolve from context (sparingly)**

```php
use Vortex\AppContext;

$container = AppContext::container();
$router = $container->make(\Vortex\Routing\Router::class);
```

## Router registration

`Router` is registered with a factory that runs **`RouteDiscovery::loadHttpRoutes($router, $basePath)`**, which loads every `app/Routes/*.php` file except `*Console.php`.
