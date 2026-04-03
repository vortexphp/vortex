# Project structure

Top-level layout for this codebase:

| Path | Role |
|------|------|
| **`app/`** | Your application: handlers, models, middleware, routes, app-only services (`Docs/`, `Uploads/`, …). Namespace **`App\`**. |
| **`engine/`** | Reusable framework (`Vortex\*`): HTTP, routing, DB layer, views, validation, console, etc. |
| **`bootstrap/app.php`** | Builds the **container**, loads config, registers the **router**, static facades (`Session`, `Translator`, …). |
| **`config/`** | PHP arrays per domain (`app`, `database`, `session`, `files`). Read with **`Repository::get`**. |
| **`public/`** | Web root: **`index.php`**, built **`css/`**, user uploads under e.g. **`uploads/`**. |
| **`assets/views/`** | Twig templates (dot names → subpaths). |
| **`assets/css/`** | Tailwind **input** (`app.css`); compiled CSS is written to **`public/css/app.css`**. |
| **`lang/`** | `en.php`, `bg.php`, … — flat or nested arrays for **`trans('key')`**. |
| **`database/`** | Class migrations in **`migrations/*.php`** applied by **`php vortex migrate`** (rollback with **`php vortex migrate:down`**). |
| **`storage/`** | Logs (`storage/logs`), Twig cache when not in debug, other writable data. |
| **`tests/`** | PHPUnit tests (`PowerCode\Tests\` for engine-style tests in this repo). |
| **`power`** | CLI entry → **`ConsoleApplication`**. |
| **`vendor/`** | Composer dependencies (do not commit if you follow `.gitignore`). |

> **Example — where a new feature touches**

- New URL → **`app/Routes/*.php`**
- Request logic → **`app/Handlers/`**
- DB entity → **`app/Models/`** + SQL under **`database/`**
- HTML → **`assets/views/`**
- Strings → **`lang/*.php`**

See also [Framework overview](../framework/README.md) for engine topics in detail.
