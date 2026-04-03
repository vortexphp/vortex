# Testing

Tests use **PHPUnit 11** with config in **`phpunit.xml.dist`**.

## Run

```bash
composer test
# same as
./vendor/bin/phpunit
```

Options such as a filter or single file:

```bash
./vendor/bin/phpunit --filter RouterGreedyParamTest
./vendor/bin/phpunit tests/RequestTest.php
```

## Layout

- **`tests/`** — test classes in namespace **`PowerCode\Tests`** (matches **`composer.json`** **`autoload-dev`**).
- **`tests/Fixtures/`** — small classes used by container tests.
- Bootstrap: **`vendor/autoload.php`** only (no full HTTP app unless a test requires it).

Coverage / static analysis are not configured by default; **`phpunit.xml.dist`** includes **`engine/`** and **`app/`** under **`<source>`** for metrics if you enable coverage.

> **Example — minimal test shape**

```php
<?php

declare(strict_types=1);

namespace PowerCode\Tests;

use PHPUnit\Framework\TestCase;

final class MyFeatureTest extends TestCase
{
    public function testSomething(): void
    {
        self::assertTrue(true);
    }
}
```

## What is covered today

Most suites target **`engine/`** (router, request, validation, crypto, config, etc.). **`DocOutlineTest`** covers docs Markdown rendering. Add **`tests/`** counterparts when you introduce non-trivial **`app/`** behavior you want to lock in.

## In-process HTTP (Kernel + synthetic request)

To assert status, body, and headers without a browser or listening socket:

1. Bootstrap your container the same way as **`public/index.php`** (e.g. **`require bootstrap/app.php`**), or use **`Vortex\Application::boot($basePath)`** if your stack matches the stock **`Application`** wiring.
2. Ensure **`ErrorRenderer`** is registered on the container (the stock **`Application::boot`** in the framework package does not bind it; the app **`bootstrap/app.php`** does).
3. Call **`Kernel::handle(Request::make('GET', '/path', $query, $body, $headers))`** (see **`Request::make`** for argument order) and inspect **`$response->httpStatus()`**, **`$response->body()`**, **`$response->headers()`**.

```php
use Vortex\Http\Kernel;
use Vortex\Http\Request;

$container = require __DIR__ . '/../bootstrap/app.php';
$kernel = new Kernel($container);
$response = $kernel->handle(Request::make('GET', '/health'));
self::assertSame(200, $response->httpStatus());
```

Use **`Request::normalizePath()`** if you reuse the same path rules outside **`Request::make()`**. For **`TRUSTED_PROXIES`** behavior in a test, call **`Vortex\Http\TrustProxies::apply()`** before **`handle()`**.

The framework’s own suite includes **`tests/KernelHandleTest.php`** and **`tests/Fixtures/minimal-http-app/`** as a minimal runnable app.

## Related

- [Console](../framework/console.md) — **`smoke`** for quick HTTP checks against a running server
- [HTTP kernel](../framework/http.md) — **`Kernel::handle`**, **`Request::make`**
