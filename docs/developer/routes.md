# Routes

## Where they live

- Put HTTP registration in **`app/Routes/*.php`**.
- The file name must **not** end with **`Console.php`** (those files are for CLI only).
- Each file is **`require`d** in name order; call **`Route::get` / `Route::post` / `Route::add`** at the top level (no wrapper closure).

Discovery runs at bootstrap: see `Vortex\Routing\RouteDiscovery::loadHttpRoutes()`.

## Registering paths

Use the **`Route`** facade (it forwards to the active `Router`):

```php
use App\Handlers\HomeHandler;
use Vortex\Http\Response;
use Vortex\Routing\Route;

Route::get('/', [HomeHandler::class, 'index']);

Route::get('/items', [ItemHandler::class, 'index'])
    ->post('/items', [ItemHandler::class, 'store']);
```

**Chaining** on the same `Route::get(...)` return value registers more methods on **different** paths (see `/items` GET + POST above).

## Named routes

After registering a route, call **`->name('unique.name')`** on the returned **`Router`**. Then build paths with **`route('unique.name', ['param' => $value])`** (global helper from the framework) or Twig **`{{ route('blog.show', { slug: post.slug }) }}`**.

Names must be unique. **`{param}`** values are URL-encoded; **`{param...}`** greedy segments are inserted verbatim (you supply the subpath).

```php
Route::get('/blog/{slug}', [BlogHandler::class, 'show'])->name('blog.show');

Route::get('/login', [LoginHandler::class, 'show'], [GuestOnly::class])
    ->name('login.show')
    ->post('/login', [LoginHandler::class, 'store'], [GuestOnly::class])
    ->name('login.store');
```

Use **`Vortex\Routing\Router::interpolatePattern()`** only if you need the same rules without the router (tests or tooling).

Query strings: **`Vortex\Support\UrlHelp::withQuery(route('blog.index'), ['page' => 2])`**.

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
