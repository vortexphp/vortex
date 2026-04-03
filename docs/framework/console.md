# Console

## Running commands

The **`power`** script (project root) boots **`ConsoleApplication`**:

> **Example — common invocations**

```bash
php power
php vortex help
php vortex migrate
php vortex doctor
php vortex db-check
```

## Built-in commands

| Command | Purpose |
|---------|---------|
| **`serve`** | PHP built-in server for **`public/`** (default `127.0.0.1:8080`, tries next port if busy) |
| **`doctor`** | Environment / production checklist; **`config/files.php`** upload **`directory`** roots under **`public/`** (exists, writable, probe) when that config exists |
| **`smoke`** | HTTP GET health checks against a base URL |
| **`db-check`** | `SELECT 1` via app **`Connection`** (loads bootstrap + `.env`) |
| **`migrate`** | Runs **`database/schema.sql`**, then **`database/patches/*.sql`** in order (skips duplicate-column errors where applicable) |

## Custom commands

1. Implement **`Vortex\Console\Command`**: **`name()`**, **`description()`**, **`run(Input $input): int`** (exit code).
2. Register in **`app/Routes/Console.php`**: file must **`return`** **`callable(ConsoleApplication): void`** and call **`$app->register(new YourCommand(...))`**.
3. Discovery: **`RouteDiscovery::loadConsoleRoutes`** loads every `app/Routes/*Console.php` file.

**`ConsoleApplication::boot`** registers built-ins first, then loads console route files.

> **Example — register a custom command**

```php
<?php

declare(strict_types=1);

use Vortex\Console\ConsoleApplication;

return static function (ConsoleApplication $app): void {
    $app->register(new App\Console\SendNewsletterCommand());
};
```

See **`engine/Console/Input.php`** and **`Term.php`** for argv parsing and terminal styling.
