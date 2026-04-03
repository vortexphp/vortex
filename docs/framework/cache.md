# Application cache

**`Vortex\Contracts\Cache`** is bound in **`bootstrap/app.php`** (and **`Application::boot`**) as the **default** store from **`CacheManager`**. After **`AppContext`** is set, **`Vortex\Cache\Cache`** is the static facade (**`Cache::remember`**, **`Cache::get`**, …) — same default store as DI. **`CacheManager`** resolves named **stores** lazily; use **`Cache::store('null')`** (or another name from config) for a non-default store.

For **blog-style usage** (index list, per-slug post, **`forget` on save**), see [Cache](../developer/cache.md).

| Config (`config/cache.php`) | `.env` |
|-----------------------------|--------|
| **`default`** | **`CACHE_STORE`** — which store name is default (falls back to **`CACHE_DRIVER`** if unset) |
| **`stores.{name}.driver`** | Per-store: **`file`** (default) or **`null`** |
| **`stores.file.path`** | **`CACHE_PATH`** — override directory (default: `storage/cache/data` under project root) |
| **`stores.file.prefix`** | **`CACHE_PREFIX`** — logical key prefix (default `vortex:`) |

Set **`CACHE_DRIVER=null`** (and optionally **`CACHE_STORE=null`**) to use **`NullCache`** as the default.

> **Example — `Cache::remember`**

```php
use Vortex\Cache\Cache;

$rows = Cache::remember('blog.recent', 300, fn () => Post::publishedRecent(10));
```

> **Example — named store**

```php
use Vortex\Cache\Cache;

Cache::store('null')->set('k', 1, null); // no-op store
```

> **Example — inject `Cache` contract (default store)**

```php
use Vortex\Contracts\Cache;

public function __construct(private readonly Cache $cache) {}

$rows = $this->cache->remember('blog.recent', 300, fn () => Post::publishedRecent(10));
```

> **Example — `get` / `set` / TTL (seconds)**

```php
use Vortex\Cache\Cache;

Cache::set('stats.post_count', $n, 600);
Cache::get('stats.post_count', 0);
Cache::forget('stats.post_count');
Cache::clear();
```

File cache is per-server and best-effort under concurrency; for multiple app servers use a shared backend later (Redis, etc.) behind the same interface.

## Related

- [Configuration](configuration.md) — how **`config/*.php`** maps to **`Repository::get`**
- [Bootstrap and container](bootstrap-and-container.md)
