# Application cache

**`Vortex\Contracts\Cache`** is bound in **`bootstrap/app.php`** (and **`Application::boot`**). After **`AppContext`** is set, **`Vortex\Cache\Cache`** is the static facade (**`Cache::remember`**, **`Cache::get`**, …) — same singleton as DI. Default implementation is **`FileCache`** (under **`storage/cache/data/`**). Set **`CACHE_DRIVER=null`** for **`NullCache`**.

For **blog-style usage** (index list, per-slug post, **`forget` on save**), see [Cache](../developer/cache.md).

| Config (`config/cache.php`) | `.env` |
|-----------------------------|--------|
| **`driver`** | **`CACHE_DRIVER`** — `file` (default) or `null` |
| **`path`** | **`CACHE_PATH`** — override directory (default: `storage/cache/data` under project root) |
| **`prefix`** | **`CACHE_PREFIX`** — logical key prefix (default `powercode:`) |

> **Example — `Cache::remember`**

```php
use Vortex\Cache\Cache;

$rows = Cache::remember('blog.recent', 300, fn () => Post::publishedRecent(10));
```

> **Example — inject `Cache` contract (same store)**

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
