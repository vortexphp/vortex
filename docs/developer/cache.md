# Cache

Config, drivers, and the **`Cache`** / **`Contracts\Cache`** API are described in [Framework: Cache](../framework/cache.md). This page shows **how to use caching against real code** in this repo (**`BlogHandler`**, **`BlogManageHandler`**, **`Post`**, **`PostComment`**).

## When to cache

- **Read-heavy, slow queries** (large lists, aggregations) where slightly stale data is acceptable.
- **Expensive derived values** (markdown render, remote API) — not yet in the default blog stack, but the same pattern applies.

Avoid caching **per-user sensitive** payloads with a global key. Prefer short TTLs or **`forget`** on writes (see below).

> **Example — public blog index (`BlogHandler::index`)**

Today the handler loads **`Post::publishedRecent(50)`** on every request. To cut database reads, wrap the query in **`Cache::remember`** with a versioned key and a TTL (seconds). Comments are not on this page, so the list can stay cached until a post is published or edited.

```php
use App\Models\Post;
use Vortex\Cache\Cache;
use Vortex\Http\Response;
use Vortex\View\View;

public function index(): Response
{
    $posts = Cache::remember('blog.index.v1', 120, static fn (): array => Post::publishedRecent(50));

    return View::html('blog.index', [
        'title' => \trans('blog.title'),
        'posts' => $posts,
    ]);
}
```

Bump **`v1`** → **`v2`** if you change the query shape or what you store. Alternatively **`forget`** the key when data changes (next example).

> **Example — post by slug (`BlogHandler::show`)**

You can cache the **`Post`** lookup and still load **comments** on every request so new comments appear immediately.

```php
use App\Models\Post;
use App\Models\PostComment;
use Vortex\Cache\Cache;

public function show(string $slug): Response
{
    $post = Cache::remember('blog.post.v1.' . $slug, 300, static function () use ($slug): ?Post {
        return Post::findPublishedBySlug($slug);
    });

    if ($post === null) {
        return View::html('errors.404', [
            'title' => \trans('errors.404.title'),
        ], 404);
    }

    $comments = PostComment::forPost((int) $post->id);
    // … build excerpt, session, View::html('blog.show', …) as today
}
```

Use a **safe key segment**: here **`$slug`** is already constrained by routing; for arbitrary strings, normalize or hash them so keys stay bounded.

> **Example — invalidate after writes (`BlogManageHandler`)**

File-backed cache does not auto-invalidate. After **`Post::create`**, **`$post->save()`**, publish/unpublish, or delete, **`forget`** the list key and any per-slug keys you use.

```php
use Vortex\Cache\Cache;

// After a successful create or update (you have $post with slug):
Cache::forget('blog.index.v1');
Cache::forget('blog.post.v1.' . (string) $post->slug);

// If the slug changed on update, also forget the old slug:
// Cache::forget('blog.post.v1.' . $previousSlug);
```

Call the same **`forget`** lines from **delete** and from any action that changes **`published_at`** or visibility.

> **Example — inject `Cache` instead of static calls**

Static **`Cache::`** matches **`DB::`** and is fine in handlers. For unit tests or shared libraries, inject **`Vortex\Contracts\Cache`** and call **`$this->cache->remember(...)`** — it is the **same** store as **`Cache::remember`**.

```php
use Vortex\Contracts\Cache;

public function __construct(
    private readonly Cache $cache,
) {
}

public function index(): Response
{
    $posts = $this->cache->remember('blog.index.v1', 120, static fn (): array => Post::publishedRecent(50));
    // …
}
```

(`Cache` here is the **interface** in **`Contracts`**; import the facade as **`use Vortex\Cache\Cache as CacheStore`** if you need both in one file.)

## Related

- [Framework: Cache](../framework/cache.md) — **`CACHE_*`**, **`FileCache`**, **`NullCache`**
- [Handlers](handlers.md) — static **`DB`** / **`Cache`** facades
- [Models](models.md) — data you load inside **`remember`** closures
