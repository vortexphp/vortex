# Support helpers

Small static utilities under **`Vortex\Support\`**.

## `Env`

**`Env::load($pathToDotenv)`** — load `.env` at bootstrap. **`Env::get('KEY', 'default')`** — string env access.

## `Log`

**`Log::exception(Throwable $e, string $basePath)`** — appends a line to **`storage/logs/app.log`** (creates directory if needed). Used from **`ErrorRenderer::exception`**.

## `UrlHelp`

- **`withQuery($path, $query)`** — append query string.
- **`withoutFragment($url)`** — strip `#...`.
- **`isInternalPath($path)`** — safe internal path check for redirects.

> **Example — login redirect with return URL**

```php
use Vortex\Support\UrlHelp;

$to = UrlHelp::withQuery('/login', ['next' => '/account']);
```

## `PathHelp`

- **`join(...$parts)`** — filesystem path join with normalization.
- **`isBelowBase($base, $candidate)`** — containment check.

> **Example**

```php
use Vortex\Support\PathHelp;

$full = PathHelp::join($basePath, 'storage', 'logs', 'app.log');
```

## `HtmlHelp`

- **`stripTags($html, $allowedTags)`**
- **`excerpt($html, $limit, $end)`** — plain-text excerpt from HTML (Twig: `excerpt_html` filter).

## `JsonHelp`

- **`encode($value, $flags)`**
- **`decodeArray($json, $depth)`** — throws on failure.
- **`tryDecodeArray($json, $depth)`** — returns null on failure.

> **Example — decode API body**

```php
use Vortex\Support\JsonHelp;

$data = JsonHelp::tryDecodeArray((string) file_get_contents('php://input'));
```

## `ArrayHelp`

Dot-key helpers: **`get`**, **`has`**, **`set`**, **`pull`**, plus **`wrap`**, **`only`**, **`except`**.

> **Example — nested read**

```php
use Vortex\Support\ArrayHelp;

$city = ArrayHelp::get($payload, 'user.address.city', '');
```

## `StringHelp`

**`slug`**, **`limit`**, **`squish`**, **`snake`**, **`camel`**, **`after`**, **`before`**, **`between`**, **`random`**.

## `NumberHelp`

**`clamp`**, **`parseInt`**, **`formatBytes`**.

## `DateHelp`

**`now($timezone)`**, **`toRfc3339`**, **`toHttpDate`**.

## `CollectionHelp`

**`keyBy`**, **`groupBy`**, **`pluck`** on arrays of rows.

> **Example — index rows by id**

```php
use Vortex\Support\CollectionHelp;

$byId = CollectionHelp::keyBy($rows, 'id');
```
