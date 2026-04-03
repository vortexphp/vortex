# Configuration

## Loading

**`Vortex\Config\Repository`** reads every **`config/*.php`** file. Each file must **`return` an array**. Keys are the filename without `.php` (e.g. `config/app.php` → `app`).

Access values with dot paths:

> **Example — read nested keys**

```php
use Vortex\Config\Repository;

Repository::get('app.name', 'App');
Repository::get('app.middleware', []);
Repository::has('database.default');
```

`Repository::setInstance()` is called from bootstrap; do not use `Repository::get` before that.

## Environment

**`Vortex\Support\Env`** loads `.env` into `$_ENV` / `putenv`. **`Env::get('KEY', 'default')`** reads a string (second argument is the default if missing).

Config files usually call `Env::get` and cast (bool, int) for typed values.

> **Example — `.env` values referenced from config**

```env
APP_DEBUG=0
APP_URL=https://example.test
TRUSTED_PROXIES=127.0.0.1
```

## Main config files

| File | Role |
|------|------|
| **`config/app.php`** | `name`, `debug`, `url`, `csp_header`, `locale`, `fallback_locale`, `locales`, **`middleware`** (global HTTP middleware class list) |
| **`config/database.php`** | PDO / connection settings for `Connection` |
| **`config/session.php`** | `name`, `lifetime`, `secure`, `samesite` (cookie params) |
| **`config/files.php`** | `max_upload_bytes`, `avatar` directory and MIME map (see [Files and uploads](files-and-uploads.md)) |
| **`config/cache.php`** | `driver` (`file` / `null`), `path`, `prefix` — see [Cache](cache.md) |
| **`config/events.php`** | `listen` — event FQCN → listener class(es); see [Events](events.md) |
| **`config/mail.php`** | `driver`, `from`, `smtp` — see [Mail](mail.md) |
| **`config/throttle.php`** | Named rate-limit profiles (`default`, `login`, …) for **`Vortex\Http\Middleware\Throttle`** — see [HTTP](http.md) |

## Content-Security-Policy

If **`app.csp_header`** is a non-empty string, **`Kernel`** adds a `Content-Security-Policy` response header after the route runs.
