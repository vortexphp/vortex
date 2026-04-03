# Errors and logging

## `ErrorRenderer`

Injectable service (registered in bootstrap with **`$basePath`**).

### `notFound()`

- If **`Request::wantsJson()`**, returns **404 JSON** with `error` / `message` keys.
- Otherwise renders **`errors.404`** Twig; on template failure, falls back to minimal HTML (still loads **`/css/app.css`** in the stub).

### `exception(Throwable $e)`

1. **`Log::exception($e, $basePath)`** — always logs to **`storage/logs/app.log`**.
2. **JSON**: generic message unless **`app.debug`**; when debug, includes message, class, file, line, trace.
3. **HTML**: **`errors.500`** with user-safe or debug message and optional trace; fallback HTML if Twig fails.

Inject **`ErrorRenderer`** in handlers for application-level 404s (missing records, etc.).

> **Example — 404 when a row is missing**

```php
use Vortex\Http\ErrorRenderer;
use Vortex\Http\Response;
use Vortex\View\View;

public function __construct(private ErrorRenderer $errors) {}

public function show(string $id): Response
{
    $item = Item::find((int) $id);
    if ($item === null) {
        return $this->errors->notFound();
    }

    return View::html('items.show', ['item' => $item, 'title' => $item->name]);
}
```

## Logging

Beyond exceptions, **`Vortex\Support\Log`** currently exposes **`Log::exception`** only. Use PHP **`error_log`** or extend logging as needed for your deployment.

## Templates

Ensure **`assets/views/errors/404.twig`** and **`errors/500.twig`** exist for themed error pages. Keys under **`lang/*/errors`** back user-facing strings in templates and JSON messages where used.
