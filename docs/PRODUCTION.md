# Production readiness

- **How to add routes, handlers, middleware, models**: [developer/README.md](developer/README.md)
- **What the stack includes (by category)**: [framework/README.md](framework/README.md)

Step-by-step checklist for deploying this stack. Check items off as you complete them.

---

## 1. Hosting & PHP

Run **`php vortex doctor`** (or **`composer doctor`**) on the server after deploy; it exits `0` only if the checks below pass.

- [ ] Web server document root points at **`public/`** only (not the repo root). Doctor verifies `public/index.php` exists.
- [ ] PHP **8.2+** on the server (Composer also requires `php:^8.2` and **`ext-pdo`**).
- [ ] At least one PDO driver: **pdo_sqlite** and/or **pdo_mysql** / **pdo_pgsql** (match `.env` `DB_DRIVER`). Doctor lists loaded drivers.
- [ ] **`storage/`** and **`storage/logs/`** are writable by the PHP/web user. Doctor tries a create/delete probe in `storage/logs/`.
- [ ] Confirm **`storage/logs/app.log`** is created after a handled error (or touch a test route that throws in staging). Doctor does not replace this; optional manual check.

---

## 2. Environment & build

- [ ] Production **`.env`** on the server (never committed): **`APP_DEBUG=0`**, real **`APP_URL`**, DB variables if used, **`MAIL_*`** for real delivery (see [framework/mail.md](framework/mail.md)) — not **`log`** unless intentional.
- [ ] Run **`php vortex doctor --production`** before or after deploy; it requires `.env`, **`APP_DEBUG` off**, **`APP_URL`** set and not localhost, **`DB_DATABASE`** if `DB_DRIVER` is not `sqlite`, plus **`vendor/autoload.php`** and a non-trivial **`public/css/app.css`** (run Tailwind build).
- [ ] Deploy install: **`composer install:prod`** (alias for `composer install --no-dev --optimize-autoloader`).
- [ ] Frontend: **`npm run build:css`** in CI or on deploy **or** commit an up-to-date **`public/css/app.css`** if Node is not on the server.

---

## 3. HTTPS & cookies (when sessions / auth exist)

- [ ] TLS enabled (certificate on host or reverse proxy).
- [ ] Session cookie flags: **Secure**, **HttpOnly**, **SameSite** as appropriate (when you add sessions; use **`Request::isSecure()`** for Secure).
- [ ] Behind a reverse proxy/CDN: set **`TRUSTED_PROXIES`** in `.env` (comma-separated IPs of your proxy, or `*` only if PHP is never reachable except via that proxy). The kernel runs **`TrustProxies::apply()`** before **`Request::capture()`**, honoring **`X-Forwarded-Proto`**, **`X-Forwarded-Host`**, and **`X-Forwarded-Port`** from trusted hops.

---

## 4. Observability

- [ ] **Log rotation** or size limits for **`storage/logs`**. Example: **`docs/deploy/logrotate-app.conf.example`** (replace `@PROJECT_ROOT@`, install under `/etc/logrotate.d/`).
- [ ] Optional: forward **`app.log`** to your host’s log aggregation.
- [ ] Uptime monitor hits **`GET /health`** (JSON `{ "ok": true }`).
- [ ] Post-deploy / CI: **`php vortex smoke`** or **`composer smoke`** (uses **`APP_URL`** or first argument; requires **`allow_url_fopen`**). Start the app or point **`APP_URL`** at staging first.

---

## 5. Security hardening

- [ ] Keep **`APP_DEBUG=0`** in production.
- [ ] Review **`Response::withSecurityHeaders()`**. Optional **Content-Security-Policy**: set **`CSP_HEADER`** in `.env` (full header value); the kernel sends it after default security headers.
- [ ] When you expose forms: CSRF, rate limiting, or WAF at the edge.

---

## 6. Database

- [ ] If not SQLite in production: set **`DB_*`** in `.env`, test connection from deploy host.
- [ ] Run **`php vortex db-check`** or **`composer db-check`** after deploy (bootstraps the app and runs **`SELECT 1`** through **`Connection`**).
- [ ] Backups scheduled for production DB.
- [ ] Schema source: start from **`database/schema.sql`** (users, posts for the blog, etc.); run **`php vortex migrate`** after changes.
- [ ] Multi-step writes that must succeed or fail together: use **`Connection::transaction()`** or **`DB::transaction()`** (same PDO; see [developer/database.md](developer/database.md)).

### Application cache (`FileCache` / `NullCache`)

- [ ] Review **`CACHE_DRIVER`**, **`CACHE_PATH`**, **`CACHE_PREFIX`** in **`.env`** (defaults in **`config/cache.php`**; API in [framework/cache.md](framework/cache.md)).
- [ ] With **`file`** (default): the web user must be able to create **`storage/cache/data/`** (under the same writable **`storage/`** as §1). Entries are not cleared on deploy unless you script it.
- [ ] If you **`Cache::remember`** mutable data (e.g. blog lists), **`Cache::forget`** the right keys on create/update/delete ([developer/cache.md](developer/cache.md)) or accept TTL-only expiry.
- [ ] **Several PHP nodes** behind a load balancer do **not** share on-disk cache; use **`CACHE_DRIVER=null`**, designate one cache writer, or add a shared **`Contracts\Cache`** implementation later (e.g. Redis).

---

## 7. Process model

- [ ] Use **PHP-FPM + nginx/Apache** (or host-managed PHP), not **`php -S`**, in production.
- [ ] Example nginx + PHP-FPM site: **`docs/deploy/nginx-site.conf.example`** (adjust **`root`**, **`fastcgi_pass`**, **`server_name`**).

---

## 8. Automation & quality (recommended)

- [ ] CI runs **`composer validate-project`** (wrapper for **`composer validate --no-check-publish`**) and **`composer test`** (PHPUnit in **`tests/`**), plus optionally **`php -l`** on changed files.
- [ ] Smoke test: **`composer smoke`** (or **`php vortex smoke https://staging.example.com`**) after deploy; same checks as §4.

---

## 9. Support helpers (step-by-step)

Use these in order when you touch the relevant area. Helpers live under **`engine/Support/`** unless noted. They keep handlers and responses consistent in production.

### 9.1 Arrays and strings (already in the repo)

**`Vortex\Support\ArrayHelp`**

1. Prefer **dot paths** for nested maps from config, JSON, or forms: read with **`ArrayHelp::get`**, write with **`ArrayHelp::set`**, existence with **`ArrayHelp::has`**, take-and-remove with **`ArrayHelp::pull`**.
2. When a value might be a scalar or a list, normalize with **`ArrayHelp::wrap`** before iterating.
3. For whitelist/blacklist field subsets, use **`ArrayHelp::only`** / **`ArrayHelp::except`** instead of repeating **`array_intersect_key`** / **`array_diff_key`** at every call site.

**`Vortex\Support\StringHelp`**

1. Build URL segments or filenames from arbitrary text with **`StringHelp::slug`** (transliteration when `intl` or `iconv` is available).
2. Truncate UI or log snippets with **`StringHelp::limit`** (uses **mbstring** when loaded).
3. Normalize free-form input with **`StringHelp::squish`** before validation or storage.
4. Map keys or env names with **`StringHelp::snake`** / **`StringHelp::camel`** when bridging APIs or config.
5. Parse delimited payloads with **`StringHelp::after`**, **`StringHelp::before`**, or **`StringHelp::between`** instead of hand-rolled `strpos` / `substr` blocks.
6. Generate opaque tokens with **`StringHelp::random`** (built on **`random_bytes`**).

### 9.2 JSON (`Vortex\Support\JsonHelp`)

1. Encode API and response bodies with **`JsonHelp::encode`** (defaults include **`JSON_UNESCAPED_UNICODE`** and **`JSON_THROW_ON_ERROR`**) — **`Response::json`** already uses this.
2. For strict parsing (must be an object/array root), use **`JsonHelp::decodeArray`** and catch **`JsonException`** (e.g. return **400** in a handler).
3. For request bodies where invalid JSON should fall back to other parsers, use **`JsonHelp::tryDecodeArray`** — **`Request::capture`** uses this for **`application/json`**.
4. Never log full user-controlled JSON blobs without truncation; summarize or cap size first.

### 9.3 URLs and redirects (`Vortex\Support\UrlHelp`)

1. Build paths with query strings using **`UrlHelp::withQuery`** (e.g. **`/login`** + **`['next' => $path]`**); it merges **`?` / `&`** and preserves **`#fragment`**.
2. Same-origin paths for redirects: **`UrlHelp::isInternalPath`** (leading **`/`**, not **`//`**); combine with your own **`APP_URL`** when you need an absolute URL.
3. Compare or canonicalize URLs for security with **`UrlHelp::withoutFragment`** so **`#`** does not skew equality checks.

### 9.4 Paths and uploads (`Vortex\Support\PathHelp`)

1. Join segments with **`PathHelp::join`** (normalizes **`\`**, skips empty/**`.`**, resolves **`..`** between segments).
2. After resolving real paths, enforce containment with **`PathHelp::isBelowBase`** (both paths must exist for **`realpath`**).
3. Keep **`LocalPublicStorage`** for public uploads; use **`PathHelp`** for general filesystem layout (logs, imports).

### 9.5 Numbers and sizes (`Vortex\Support\NumberHelp`)

1. Bound numeric config and user input with **`NumberHelp::clamp`** before SQL limits, pagination, or caps.
2. Show sizes in UI or logs with **`NumberHelp::formatBytes`** for consistent **KB** / **MB** labels.
3. Parse query or form integers with **`NumberHelp::parseInt`** (decimal digits only, clamped to **`$min`–`$max`**; non-numeric → **`$default`**).

### 9.6 Dates and time (`Vortex\Support\DateHelp`)

1. Use **`DateTimeImmutable`**; **`DateHelp::now`** optionally takes an IANA timezone string, otherwise PHP’s default timezone.
2. APIs: **`DateHelp::toRfc3339`** (**`ATOM`**). HTTP **`Date`** header: **`DateHelp::toHttpDate`**.
3. Store UTC in the database; convert to local time at the presentation edge (pass the display timezone into **`DateHelp::now`** or **`DateTimeZone`** when formatting).

### 9.7 HTML snippets (`Vortex\Support\HtmlHelp`)

1. Twig escapes **`{{ }}`** output by default; use **`|raw`** only for trusted markup. The **`nl2br_e`** filter escapes then applies **`nl2br`**. Listing previews in templates: **`|excerpt_html(limit)`** (wraps **`HtmlHelp::excerpt`**).
2. Allowlisted markup: **`HtmlHelp::stripTags`** with a list of tag names (e.g. **`['p','br']`**).
3. PHP helpers **`e()`** / **`trans()`** remain available in non-Twig code and in **`AppTwigExtension`** (`trans`, **`public_url`**, etc.).

### 9.8 Lists of rows (`Vortex\Support\CollectionHelp`)

1. From **`list<array<string,mixed>>`** (typical SQL rows), use **`CollectionHelp::keyBy`** (last row wins on duplicate keys), **`groupBy`**, or **`pluck`** instead of ad-hoc loops.
2. Document row shapes in PHPDoc (`list<array{...}>`) so static analysis matches the helper.

### 9.9 Crypto and doctor copy (already in the repo)

**`Vortex\Crypto\Password`**, **`Crypt`**, **`SecurityHelp`**

1. Passwords: only **`Password::hash`** / **`Password::verify`** (never **`Crypt`** for credentials).
2. Tamper-evident tokens or payloads: **`Crypt::hash`** / **`Crypt::verify`** with **`APP_KEY`**.
3. Run **`php vortex doctor`** and read the **Crypto** section; **`SecurityHelp::namespaceGuide()`** mirrors that text for code review and onboarding.

---

## 10. Models and database access

Domain rows are **`App\Models\*`** classes extending **`Vortex\Database\Model`** (Active Record–style: **`find`**, **`create`**, **`update($attrs)`**, **`save()`**, **`delete()`**, **`$fillable`**, timestamps). Use **`SomeModel::query()->where(…)->whereIn(…)->orderByDesc(…)->offset(…)->limit(…)->get()`**; the builder also supports **`count()`**, **`exists()`**, and **`paginate($page, $perPage)`** (see **`engine/Database/QueryBuilder.php`** — single-table **`SELECT`** only; column names must never come from raw user input).

**`Vortex\Database\Connection`** (PDO) backs models and is also used for **`php vortex db-check`** and **`php vortex migrate`**. The static **`Vortex\Database\DB`** class resolves the same singleton (e.g. **`DB::transaction`**, **`DB::select`**) after bootstrap. Application cache: **`Vortex\Contracts\Cache`** and static **`Vortex\Cache\Cache`** (**`Cache::remember`**, …) — see §6 above, [framework/cache.md](framework/cache.md), [developer/cache.md](developer/cache.md). Events: **`Vortex\Events\Dispatcher`** and **`EventBus::dispatch`** — [framework/events.md](framework/events.md), [developer/events.md](developer/events.md). Mail: **`Mailer`**, **`Mail::send`**, **`MailMessage`** — [framework/mail.md](framework/mail.md), [developer/mail.md](developer/mail.md). Schema source: **`database/schema.sql`**.

---

## 11. Framework evolution (recommended)

These items are **not** required to ship, but they close common gaps in **`engine/`** as the product grows. Prioritize in roughly this order unless a feature forces otherwise.

### High impact

1. ~~**Database transactions**~~ — **`Connection`** and **`DB`**: **`beginTransaction`**, **`commit`**, **`rollBack`**, **`inTransaction`**, **`transaction(callable)`** (see [developer/database.md](developer/database.md)).

2. ~~**Application cache**~~ — **`Contracts\Cache`** + **`Cache::`**, **`FileCache`** / **`NullCache`** ([framework/cache.md](framework/cache.md), [developer/cache.md](developer/cache.md)). Redis-style drivers can implement the same contract later.

3. ~~**Event dispatcher**~~ — **`Dispatcher`**, **`EventBus::dispatch`**, **`config/events.php`** → **`events.listen`** ([framework/events.md](framework/events.md), [developer/events.md](developer/events.md)). Listeners run **synchronously**; add a queue later for slow work.

4. ~~**Outbound mail**~~ — **`Contracts\Mailer`**, **`Mail::send`**, drivers **`log`**, **`null`**, **`native`** (`mail()`), **`smtp`** ([framework/mail.md](framework/mail.md), [developer/mail.md](developer/mail.md)).

5. **Rate limiting middleware** — Complements CSRF and security headers; throttle login, contact, and similar endpoints by IP or session (or rely on edge WAF — see §5).

### Developer experience

6. **Named routes and URL generation** — **`UrlHelp`** today builds query strings only; named routes (or a **`route('name', $params)`** helper) reduce duplication when paths change.

7. **Pagination in the UI** — **`QueryBuilder::paginate()`** already exists; standardize list pages with shared Twig partials or a small view model so every listing behaves the same.

8. **HTTP testing harness** — A path that runs **`Kernel`** (or equivalent) with a synthetic **`Request`** and asserts on **`Response`** makes handler and middleware tests realistic without a browser (see [developer/testing.md](developer/testing.md)).

### When you need them

9. **Job queue** — Even a DB- or file-backed queue plus **`php vortex queue:work`** defers slow work (emails, imports) once mail/events exist.

10. **Scheduler** — A console entry point runnable from cron for recurring tasks, if you add background jobs.

11. **Static analysis in CI** — PHPStan or Psalm (and optional coverage gates) alongside **`composer test`** catches regressions PHPUnit alone may miss.

---

## Progress notes

Use this section to jot dates, blockers, or decisions as you go.

| Step | Date | Notes |
|------|------|--------|
| 1    |      | `vortex doctor` + `ext-pdo` in composer |
| 2    |      | `doctor --production`, `composer install:prod` |
| 3    |      | `TRUSTED_PROXIES`, `TrustProxies`, `Request::isSecure()` |
| 4    |      | logrotate example, `vortex smoke`, `composer smoke` |
| 5    |      | `CSP_HEADER` → kernel |
| 6    |      | `db-check`, `database/schema.sql`, `CACHE_*`, `storage/cache/data` |
| 7    |      | nginx example in `docs/deploy/` |
| 8    |      | `composer validate-project`, `composer test` |
| 9    |      | `ArrayHelp`, `StringHelp`, `JsonHelp`, `UrlHelp`, `PathHelp`, `NumberHelp`, `DateHelp`, `HtmlHelp`, `CollectionHelp` |
| 10   |      | `App\Models`, `Model`, `QueryBuilder`, `Connection`, `DB`, `Cache`, `Dispatcher`, `EventBus`, `Mailer`, `Mail` |
| 11   |      | Framework evolution backlog (§11) |
