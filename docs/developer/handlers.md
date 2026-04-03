# Handlers

Handlers are plain PHP classes the **container instantiates** when a route matches.

## Conventions

- Place them under **`app/Handlers/`** (subfolders allowed, e.g. `Auth/`).
- Use **`final class`** unless you intend subclassing.
- Each **action method** should return a **`Vortex\Http\Response`** (or, in a pinch, a string or array — the router wraps them; prefer `Response` explicitly).

## Typical patterns

**HTML page** (Twig):

```php
use Vortex\Http\Response;
use Vortex\View\View;

public function index(): Response
{
    return View::html('items.index', [
        'title' => trans('items.title'),
        'items' => Item::query()->orderByDesc('id')->limit(20)->get(),
    ]);
}
```

Template name uses dots: **`items.index`** → `assets/views/items/index.twig`.

**Redirect**:

```php
use Vortex\Http\Response;

return Response::redirect('/blog', 302);
```

**JSON**:

```php
return Response::json(['ok' => true], 201);
```

**Not found** (Twig error page or JSON for APIs):

```php
use Vortex\Http\ErrorRenderer;

// Injected: public function __construct(private ErrorRenderer $errors) {}

public function show(string $slug): Response
{
    $row = Post::findBySlug($slug);
    if ($row === null) {
        return $this->errors->notFound();
    }

    return View::html('blog.show', ['post' => $row, 'title' => $row->title]);
}
```

`ErrorRenderer::notFound()` respects `Accept: application/json` and returns a JSON body when appropriate.

## Reading the request

Route parameters arrive as **method arguments**. For query string, body, and headers use **`Request`** (and helpers on **`Session`**, **`Csrf`**, etc.):

```php
use Vortex\Http\Request;

$page = (int) (Request::query()['page'] ?? 1);
$email = (string) Request::input('email', '');
$path = Request::path(); // current path, e.g. /account/edit
```

## HTML forms: CSRF, validation, flash, redirect

Typical POST flow (see `App\Handlers\BlogManageHandler`, `Auth\LoginHandler`):

```php
use Vortex\Http\Csrf;
use Vortex\Http\Request;
use Vortex\Http\Response;
use Vortex\Http\Session;
use Vortex\Validation\Validator;

public function store(): Response
{
    if (! Csrf::validate()) {
        Session::flash('errors', ['_form' => trans('auth.csrf_invalid')]);

        return Response::redirect('/items/new', 302);
    }

    $data = [
        'title' => (string) Request::input('title', ''),
        'body' => (string) Request::input('body', ''),
    ];

    $validation = Validator::make(
        $data,
        ['title' => 'required|string|max:120', 'body' => 'required|string|max:50000'],
        [
            'title.required' => trans('items.validation.title'),
            'body.required' => trans('items.validation.body'),
        ],
    );

    if ($validation->failed()) {
        Session::flash('errors', $validation->errors());
        Session::flash('old', $data);

        return Response::redirect('/items/new', 302);
    }

    Item::create(/* … */);

    return Response::redirect('/items', 302);
}
```

In Twig, render `errors` / `old` from the session on the GET form page (or pass them from the handler when you re-display without redirect).

## Constructor injection

The container builds handlers with **reflection**: any **constructor parameter** typed with a **class** that the container can `make()` is injected automatically. Example:

```php
public function __construct(
    private readonly SomeService $service,
) {
}
```

Register **`SomeService`** in **`bootstrap/app.php`** with `$container->singleton(SomeService::class, ...)`. If the handler has **no constructor** or only default values, it still works (see `HomeHandler`).

## Static facades (`DB`, `Cache`)

After **`AppContext::set()`** (normal HTTP / full bootstrap), you can call the same singletons the container would inject:

| Class | Use for | Detail |
|-------|---------|--------|
| **`Vortex\Database\DB`** | SQL, transactions | [Database](database.md) |
| **`Vortex\Cache\Cache`** | **`Cache::remember`**, **`get`**, **`set`**, … | [Cache](cache.md), [Framework: Cache](../framework/cache.md) |
| **`Vortex\Events\EventBus`** | **`EventBus::dispatch($event)`** | [Events](events.md), [Framework: Events](../framework/events.md) |
| **`Vortex\Mail\Mail`** | **`Mail::send`**, **`Mail::defaultFrom()`** | [Mail](mail.md), [Framework: Mail](../framework/mail.md) |

Prefer **constructor injection** when the handler already has dependencies; use static calls for one-offs or tiny handlers.

```php
use Vortex\Cache\Cache;
use Vortex\Database\DB;
use Vortex\Events\EventBus;

$posts = Cache::remember('blog.home', 120, fn () => Post::publishedRecent(5));

DB::transaction(function ($db) use ($id): void {
    $db->execute('UPDATE posts SET published_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $id]);
});

// After persisting: EventBus::dispatch(new UserRegistered($user));
```
