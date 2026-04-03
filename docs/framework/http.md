# HTTP

## Kernel

**`Vortex\Http\Kernel::send()`**:

1. **`TrustProxies::apply()`** — may adjust `$_SERVER` when behind a trusted proxy (see below).
2. **`Request::capture()`** then **`Kernel::handle($request)`** (same pipeline as below, then **`$response->send()`**).

**`Kernel::handle(Request $request): Response`** (no output): runs steps 3–6 for a request you build yourself (e.g. **`Request::make()`** in PHPUnit). Does **not** call **`TrustProxies::apply()`**; call it first if the test must honor **`TRUSTED_PROXIES`**.

3. **`Router::dispatch()`** with **`Repository::get('app.middleware', [])`** as global middleware.
4. On any **`Throwable`**, **`ErrorRenderer::exception()`** produces the response.
5. **`$response->withSecurityHeaders()`** — default security headers if not already set.
6. Optional **CSP** from config (see [Configuration](configuration.md)).

## Router (named paths)

Register paths with **`Vortex\Routing\Route`**, assign a name with **`->name('blog.show')`**, then build URLs using the global **`route()`** helper or Twig **`route()`**. See [developer/routes.md](../developer/routes.md#named-routes).

## TrustProxies

When the app is behind a reverse proxy or CDN, set **`TRUSTED_PROXIES`** in `.env`:

- Comma-separated IPs that may send `X-Forwarded-*`, or **`*`** only if PHP is never exposed except through the proxy.
- If `REMOTE_ADDR` is trusted, **`X-Forwarded-Proto`**, **`X-Forwarded-Host`**, and **`X-Forwarded-Port`** adjust HTTPS detection and host/port for **`Request`**.

> **Example — trust one proxy IP**

```env
TRUSTED_PROXIES=10.0.0.1
```

## Request

**`Request::capture()`** reads superglobals (used by **`Kernel::send()`**). **`Request::make($method, $path, …)`** builds a request for tests without **`$_SERVER`**. **`Request::normalizePath()`** applies the same path rules as **`capture()`**.

Static API on the current request (set during **`Kernel::handle()`** / **`send()`**):

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
| `cookie($name, $default)` | Value from the **`Cookie`** request header |
| `cookies()` | All parsed cookies (`name => value`) |

> **Example — typical reads in a handler**

```php
use Vortex\Http\Request;

$q = Request::query();
$page = max(1, (int) ($q['page'] ?? 1));
$email = (string) Request::input('email', '');
$token = Request::header('X-Api-Token');
$theme = Request::cookie('theme');

if (Request::wantsJson()) {
    // …
}
```

## Cookie (HTTP) vs Session

- **`Vortex\Http\Cookie`** — value object for **application** **`Set-Cookie`** headers. Build with **`new Cookie($name, $value, …)`** (path, domain, **`maxAge`**, **`expires`**, **`secure`**, **`httpOnly`**, **`sameSite`**), then **`$response->cookie($cookie)`** or **`Cookie::queue($cookie)`** (merged by **`Kernel::handle()`** and **`Application::run()`** via **`Cookie::flushQueued()`** before the response is returned or sent). **`Cookie::parseRequestHeader()`** is used internally when building **`Request`** from the **`Cookie`** header. **`Cookie::normalizedSameSite()`** is shared with **`Session`** for consistent **`SameSite`** spelling.
- **`Vortex\Http\Session`** — facade over a store from **`SessionManager`** (`config/session.php`: `default` + `stores.{name}.driver`). The `native` driver uses server-side **`$_SESSION`** data; the session **id** is still an HTTP cookie issued by PHP via **`session_set_cookie_params()`**, not via **`Cookie::toHeaderValue()`**.

To clear an app cookie, send **`new Cookie($name, '', maxAge: 0)`** (and matching **`path`** / **`domain`**).

## Response

| Factory / method | Purpose |
|------------------|---------|
| `Response::html($body, $status, $headers)` | HTML |
| `Response::json($data, $status)` | JSON |
| `Response::redirect($url, $status)` | Redirect |
| `->header($name, $value)` | Fluent header |
| `->cookie(Cookie $cookie)` | Append **`Set-Cookie`** (multiple allowed) |
| `->headers()` | Current header map (for tests / inspection) |
| `->withSecurityHeaders()` | X-Content-Type-Options, Referrer-Policy, X-Frame-Options |

> **Example — JSON + custom header**

```php
use Vortex\Http\Response;

return Response::json(['items' => $rows])
    ->header('Cache-Control', 'private, max-age=60')
    ->withSecurityHeaders();
```

## Session

**`Vortex\Http\Session`** is a facade for the default store from **`SessionManager`**. Use **`Session::store('name')`** to access a named store. With `native`, session **cookie** (name, lifetime, **`secure`**, **`SameSite`**, **`HttpOnly`**) is configured from **`config/session.php`** and applied via **`session_set_cookie_params()`**. Prefer **`Cookie`** + **`Response::cookie()`** for things like “remember theme” or tracking cookies that are not session keys.

| Method | Purpose |
|--------|---------|
| `get` / `put` / `forget` / `pull` | Session bag |
| `flash($key)` read; `flash($key, $value)` write | One-request flash |
| `regenerate()` | New session id |
| `authUserId()` | Normalized `auth_user_id` or null |
| `flushAuth()` | Clears `auth_user_id` and regenerates id |

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
