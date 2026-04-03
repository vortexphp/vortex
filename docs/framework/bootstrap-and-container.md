# Bootstrap and container

## Entry points

- **HTTP**: `public/index.php` loads `bootstrap/app.php`, builds `Kernel`, calls `send()`.
- **CLI**: `power` runs `ConsoleApplication::boot($basePath)->run($argv)` (no full HTTP bootstrap unless a command loads it, e.g. `migrate`).

## `bootstrap/app.php`

Typical responsibilities:

1. Load Composer autoload, then **`Log::setBasePath($basePath)`** and **`Storage::setBasePath($basePath)`** so logging and **`config/storage.php`** disks resolve (see [Files and uploads](files-and-uploads.md)).
2. Load **`.env`** via **`Env::load()`**.
3. Create **`Container`**, register itself: `instance(Container::class, $container)`.
4. Register **singletons**: `Repository`, `DatabaseManager`, `Connection` (default PDO connection from the manager), `CacheManager`, `Contracts\Cache` (default store), `Dispatcher`, `Mailer`, `SessionManager`, `Session` (default store), `Csrf`, `LocalPublicStorage`, `Translator`, Twig `Factory`, `Router`, `ErrorRenderer`, and any app classes (e.g. docs services).
5. Call **`Repository::setInstance`**, **`Session::setInstance`**, **`Csrf::setInstance`**, **`LocalPublicStorage::setInstance`**, **`Translator::setInstance`**, **`View::useFactory(...)`**, **`AppContext::set($container)`**.

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

After bootstrap, **`AppContext::container()`** returns the same container from anywhere (e.g. rare cases outside DI). Prefer constructor injection in handlers and middleware. **`Vortex\Database\DB`** forwards to the default **`Connection`** from **`DatabaseManager`** (e.g. **`DB::transaction`**; **`DB::connection('name')`** for others) — see [developer/database.md](../developer/database.md). **`Vortex\Cache\Cache`** uses **`CacheManager`** and the default **`Contracts\Cache`** store (e.g. **`Cache::remember`**; **`Cache::store('name')`**) — see [Cache](cache.md). **`Vortex\Http\Session`** uses **`SessionManager`** and default store (e.g. **`Session::get`**; **`Session::store('name')`**) — see [HTTP](http.md). **`Vortex\Events\EventBus`** forwards to **`Dispatcher`** — see [Events](events.md). **`Vortex\Mail\Mail`** forwards to **`Mailer`** — see [Mail](mail.md).

> **Example — resolve from context (sparingly)**

```php
use Vortex\AppContext;

$container = AppContext::container();
$router = $container->make(\Vortex\Routing\Router::class);
```

## Router registration

`Router` is registered with a factory that runs **`RouteDiscovery::loadHttpRoutes($router, $basePath)`**, which **`require`s** every `app/Routes/*.php` file (top-level **`Route::`…** calls) except `*Console.php`.
