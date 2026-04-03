# HTTP

## Kernel

**`Vortex\Http\Kernel::send()`**:

1. **`TrustProxies::apply()`** — may adjust `$_SERVER` when behind a trusted proxy (see below).
2. **`Request::capture()`** and **`Request::setCurrent()`**.
3. **`Router::dispatch()`** with **`Repository::get('app.middleware', [])`** as global middleware.
4. On any **`Throwable`**, **`ErrorRenderer::exception()`** produces the response.
5. **`$response->withSecurityHeaders()`** — default security headers if not already set.
6. Optional **CSP** from config (see [Configuration](configuration.md)).
7. **`$response->send()`**.

## TrustProxies

When the app is behind a reverse proxy or CDN, set **`TRUSTED_PROXIES`** in `.env`:

- Comma-separated IPs that may send `X-Forwarded-*`, or **`*`** only if PHP is never exposed except through the proxy.
- If `REMOTE_ADDR` is trusted, **`X-Forwarded-Proto`**, **`X-Forwarded-Host`**, and **`X-Forwarded-Port`** adjust HTTPS detection and host/port for **`Request`**.

> **Example — trust one proxy IP**

```env
TRUSTED_PROXIES=10.0.0.1
```

## Request

Static API on the current request (set during `Kernel::send()`):

| Method | Purpose |
|--------|---------|
| `method()` | HTTP method |
| `path()` | Path (e.g. `/blog`) |
| `query()` | Query array |
| `body()` | Parsed body (form/json as implemented) |
| `input($key, $default)` | Merged query + body |
| `all()` | Full merged input |
| `file($key)` | `UploadedFile` or null |
| `files()` | All uploads |
| `header($name, $default)` | Header |
| `headers()`, `server()` | Raw arrays |
| `wantsJson()` | `Accept` prefers JSON (API errors) |
| `isSecure()` | HTTPS |

> **Example — typical reads in a handler**

```php
use Vortex\Http\Request;

$q = Request::query();
$page = max(1, (int) ($q['page'] ?? 1));
$email = (string) Request::input('email', '');
$token = Request::header('X-Api-Token');

if (Request::wantsJson()) {
    // …
}
```

## Response

| Factory / method | Purpose |
|------------------|---------|
| `Response::html($body, $status, $headers)` | HTML |
| `Response::json($data, $status)` | JSON |
| `Response::redirect($url, $status)` | Redirect |
| `->header($name, $value)` | Fluent header |
| `->withSecurityHeaders()` | X-Content-Type-Options, Referrer-Policy, X-Frame-Options |

> **Example — JSON + custom header**

```php
use Vortex\Http\Response;

return Response::json(['items' => $rows])
    ->header('Cache-Control', 'private, max-age=60')
    ->withSecurityHeaders();
```

## Session

**`Vortex\Http\Session`** is a facade backed by PHP sessions.

| Method | Purpose |
|--------|---------|
| `get` / `put` / `forget` / `pull` | Session bag |
| `flash($key)` read; `flash($key, $value)` write | One-request flash |
| `regenerate()` | New session id |
| `authUserId()` | Normalized `auth_user_id` or null |
| `flushAuth()` | Clears `auth_user_id` and regenerates id |

Cookie name, lifetime, `secure`, and `SameSite` come from **`config/session.php`** (and env vars wired there).

> **Example — flash then redirect**

```php
use Vortex\Http\Response;
use Vortex\Http\Session;

Session::flash('status', 'Saved.');
return Response::redirect('/dashboard', 302);
```

## CSRF

**`Csrf::token()`** returns (and stores) a per-session token. **`Csrf::validate()`** compares **`Request::input('_csrf')`** to that token with `hash_equals`. Use a hidden field **`_csrf`** on HTML forms POSTs.

> **Example — hidden field in Twig**  
> This project shares **`csrfToken`** from **`ShareAuthUser`** middleware on every request.

```twig
<input type="hidden" name="_csrf" value="{{ csrfToken|default('') }}">
```

## Rate limiting (Throttle)

**`Vortex\Http\Middleware\Throttle`** counts requests per client IP in a fixed time window using **`Contracts\Cache`**. Profiles live in **`config/throttle.php`** (`throttle.login`, `throttle.default`, …) as **`max_attempts`** and **`decay_seconds`**.

Subclass **`Throttle`** and implement **`profile()`** (return the profile name), then add the class to a route’s middleware list (often on **POST** only). Example: **`App\Middleware\ThrottleLogin`** on **`POST /login`**.

On exceed, responds **429** with **`Retry-After`**. JSON clients (**`Accept: application/json`**) get **`{"message":"Too Many Requests"}`**.

If **`CACHE_DRIVER=null`**, the cache does not persist entries; throttling has no practical effect until you use a persistent cache driver.

## Uploaded files

See **`Request::file('field')`** → **`UploadedFile`** in [Files and uploads](files-and-uploads.md).
