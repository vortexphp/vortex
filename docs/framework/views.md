# Views

## Twig layout

- Templates live under **`assets/views/`**.
- Names use dots: **`blog.show`** → `assets/views/blog/show.twig`.
- **`Vortex\View\View::html($name, $data)`** returns a **`Response`** with rendered HTML.
- **`View::render($name, $data)`** returns a string (used inside **`ErrorRenderer`**, etc.).

> **Example — handler returns a page**

```php
use Vortex\Http\Response;
use Vortex\View\View;

return View::html('blog.index', [
    'title' => trans('blog.title'),
    'posts' => $posts,
]);
```

## Factory and cache

Bootstrap registers **`Vortex\View\Factory`** with:

- Path: **`assets/views`**
- Debug flag from **`app.debug`**
- When not in debug mode, Twig cache directory: **`storage/cache/twig`**

## Shared data

- **`View::share('key', $value)`** — available on every render (same as the factory’s shared bag).
- Bootstrap may share app-wide keys (e.g. `appName`).

## Twig extension (`AppTwigExtension`)

Functions:

| Function | Purpose |
|----------|---------|
| `trans(key, replace)` | Same as PHP `trans()` |
| `public_url(relative)` | URL path under `public/` (leading slash) |
| `url_query(path, query)` | Path with query string (`UrlHelp::withQuery`) |
| `server_now()` | Current `Y-m-d H:i:s` |
| `session_flash(key)` | Read flash value in Twig |

Filters:

| Filter | Purpose |
|--------|---------|
| `excerpt_html` | `HtmlHelp::excerpt` — plain text excerpt from HTML |
| `paragraphs` | Split body into paragraph strings |
| `nl2br_e` | Escape then `nl2br` (safe Markup) |

Twig test: **`string`** — `value is string`.

> **Example — translate, link with query, excerpt**

```twig
<h1>{{ trans('blog.latest') }}</h1>
<a href="{{ url_query('/blog', { tag: activeTag }) }}">{{ trans('blog.filter') }}</a>
<p>{{ post.body_html|excerpt_html(200) }}</p>
```

See **`engine/View/Twig/AppTwigExtension.php`** for details.

## Global helpers (PHP)

From **`engine/I18n/helpers.php`**: **`trans()`**, **`e()`** (escape), **`public_url()`**.

> **Example — building a public asset URL in PHP**

```php
$url = public_url('uploads/avatars/' . $user->avatar_path);
```
