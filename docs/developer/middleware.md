# Middleware

## Contract

Implement **`Vortex\Contracts\Middleware`**:

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Vortex\Contracts\Middleware;
use Vortex\Http\Request;
use Vortex\Http\Response;

final class RequireExample implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (/* condition fails */) {
            return Response::redirect('/login', 302);
        }

        return $next($request);
    }
}
```

- Call **`$next($request)`** to continue the pipeline.
- Return your own **`Response`** to short-circuit (redirect, 403 HTML, JSON error, etc.).

**Require signed-in user** (same idea as `App\Middleware\RequireAuth`):

```php
use Closure;
use Vortex\Http\Request;
use Vortex\Http\Response;
use Vortex\Http\Session;
use Vortex\Support\UrlHelp;

public function handle(Request $request, Closure $next): Response
{
    if (Session::authUserId() === null) {
        return Response::redirect(
            UrlHelp::withQuery('/login', ['next' => Request::path()]),
            302,
        );
    }

    return $next($request);
}
```

**API-style guard** (if you need JSON instead of redirect, branch on `Request::wantsJson()` and return `Response::json([...], 401)`).

## Rate limiting

Subclass **`Vortex\Http\Middleware\Throttle`**, implement **`profile()`** (must match a key in **`config/throttle.php`**), and add the class to route middleware (see [framework/http.md](../framework/http.md#rate-limiting-throttle)).

## Registration

- **Single route**: third argument to `Route::get` / `Route::post` (array of class names).
- **Every request**: append the class name to **`config/app.php`** → **`middleware`**.

## Sharing data with views

From middleware (or anywhere after bootstrap), use:

```php
use Vortex\View\View;

View::share('key', $value);
```

Twig templates receive shared keys on every render (same as handler-passed data).
