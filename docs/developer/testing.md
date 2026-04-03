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

## Related

- [Console](../framework/console.md) — **`smoke`** for quick HTTP checks against a running server
