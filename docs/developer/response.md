# Response

Handlers (and middleware that short-circuits) return **`Vortex\Http\Response`**. Build them with static factories and optional fluent **`header()`** / **`withSecurityHeaders()`** (applied in the kernel before **`send()`** for security defaults).

## HTML

> **Example**

```php
use Vortex\Http\Response;
use Vortex\View\View;

return View::html('blog.index', ['title' => $title, 'posts' => $posts]);
// same end result as Response::html($twigString, 200) if you already have a string
```

## JSON

> **Example — API-style payload**

```php
return Response::json(['ok' => true, 'id' => $id], 201);
```

Use together with **`Request::wantsJson()`** when branching between HTML and JSON (see **`ErrorRenderer`**).

## Redirects

> **Example**

```php
return Response::redirect('/blog', 302);
```

Use **`UrlHelp::withQuery()`** when you need query parameters (e.g. login **`next`**).

## Custom status and headers

> **Example**

```php
return Response::html($body, 403)
    ->header('Cache-Control', 'no-store');
```

## What the kernel adds

After your handler runs, **`Kernel`** applies **`withSecurityHeaders()`** and, if configured, the **CSP** header from **`app.csp_header`**. See [Framework: HTTP](../framework/http.md).

## Related

- [Handlers](handlers.md) — redirects after validation, CSRF failures  
- [Framework: HTTP](../framework/http.md) — **`Request`**, **`Response`** table, session  
