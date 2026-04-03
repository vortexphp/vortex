# Routes

## Where they live

- Put HTTP registration in **`app/Routes/*.php`**.
- The file name must **not** end with **`Console.php`** (those files are for CLI only).
- Each file must **`return` a callable** with signature **`callable(): void`** (no arguments). Inside it, call `Route::get` / `Route::post` / `Route::add`.

Discovery runs at bootstrap: see `Vortex\Routing\RouteDiscovery::loadHttpRoutes()`.

## Registering paths

Use the **`Route`** facade (it forwards to the active `Router`):

```php
use App\Handlers\HomeHandler;
use Vortex\Http\Response;
use Vortex\Routing\Route;

return static function (): void {
    Route::get('/', [HomeHandler::class, 'index']);

    Route::get('/items', [ItemHandler::class, 'index'])
        ->post('/items', [ItemHandler::class, 'store']);
};
```

**Chaining** on the same `Route::get(...)` return value registers more methods on **different** paths (see `/items` GET + POST above).

**Closure** action (same parameter order as `{placeholders}`):

```php
use Vortex\Http\Response;

Route::get('/hello/{name}', static function (string $name): Response {
    return Response::json(['hello' => $name]);
});
```

**Several placeholders** map left‑to‑right to the handler signature:

```php
// Route::get('/users/{userId}/posts/{postId}', [PostHandler::class, 'show']);
// public function show(string $userId, string $postId): Response
```

**Non-GET/POST** via `Route::add`:

```php
use App\Middleware\RequireAuth;

Route::add(['PUT', 'PATCH'], '/profile', [ProfileHandler::class, 'update'], [RequireAuth::class]);
```

- **Pattern**: leading slash, static segments, and **`{param}`** placeholders (letters, digits, underscore). Example: `/blog/manage/posts/{id}/edit` → handler method receives **`(string $id)`** (or `int` if you cast inside the handler).
- **Methods**: `Route::get`, `Route::post`, or `Route::add(['GET','PUT'], $pattern, $action, $middleware)`.
- **Action** (second argument):
  - **`[SomeHandler::class, 'methodName']`** — the container builds `SomeHandler` and calls `methodName` with route parameters **in order**.
  - **`Closure`** — `fn (string $slug): Response => ...` with the same parameter order as `{slug}` in the path.

## Per-route middleware

Pass a **list of middleware class names** as the **third** argument:

```php
use App\Middleware\RequireAuth;

Route::get('/account', [AccountHandler::class, 'index'], [RequireAuth::class]);
```

Order: **global** middleware from `config/app.php` → **`app.middleware`** runs first, then route middleware. The router resolves each middleware with **`$container->make(YourMiddleware::class)`**.

## Global middleware

Edit **`config/app.php`**, key **`middleware`**: array of class names implementing `Vortex\Contracts\Middleware`. They run on **every** HTTP request (before route middleware).

## Multi-segment parameters

Use **`{name...}`** (three dots inside the braces) to capture the rest of the path, including slashes. Example: `/docs/{path...}` matches `/docs/framework/routing` with `path` = `framework/routing`. Register a more specific route like `/docs` **before** the greedy one so the index path is not swallowed.
