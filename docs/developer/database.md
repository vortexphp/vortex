# Database

The app uses **`Vortex\Database\DatabaseManager`** and a default **`Vortex\Database\Connection`** (PDO) from **`config/database.php`** (**`default`** + **`connections`** with per-connection **`driver`**). For static calls after bootstrap, use **`Vortex\Database\DB`** (default connection: **`DB::select`**, **`DB::execute`**, **`DB::transaction`**, …). Use **`DB::connection('name')`** for another configured connection. Models extend **`Model`** and use the **default** connection only — see [Models](models.md).

## Schema layout

| Artifact | Purpose |
|----------|---------|
| **`database/schema.sql`** | Baseline **`CREATE TABLE IF NOT EXISTS`** (and indexes). Run first on **`migrate`**. |
| **`database/patches/*.sql`** | Incremental changes; executed in **sorted filename order** after `schema.sql`. |

Naming patches with a numeric prefix keeps order obvious, e.g. **`001_add_posts_published_at.sql`**.

## Applying changes

```bash
php vortex migrate
```

This loads **`bootstrap/app.php`**, opens PDO, runs **`schema.sql`**, then each **`patches/*.sql`**. Duplicate-column errors in patches are ignored so re-runs are safer for idempotent patches.

> **Tip**  
> Keep **`schema.sql`** aligned with the full desired schema when you use it as a fresh-install baseline; use patches for everything that already shipped to production.

## SQLite vs MySQL

Connection settings come from **`.env`** / **`config/database.php`**. **`DB_CONNECTION`** selects the default connection name (the shipped config defines **`default`** with **`DB_DRIVER`** and related vars). Use SQL compatible with your driver (this project often uses SQLite locally; adjust types if you use MySQL in production).

## Checking connectivity

```bash
composer db-check
# or
php vortex db-check
```

## Transactions

**`DB`** and an injected **`Connection`** use the same PDO (via **`AppContext::container()`**). **`DB::transaction()`** / **`Connection::transaction()`** commit on success, roll back on any **`Throwable`**, then rethrow. Models and **`QueryBuilder`** join that transaction automatically.

> **Example — `DB::transaction()` (no constructor injection)**

```php
use App\Models\PostComment;
use Vortex\Database\DB;

DB::transaction(function ($db) use ($postId, $author, $body): void {
    PostComment::createForPost($postId, $author, $body);
    $db->execute('UPDATE posts SET updated_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $postId]);
});
```

The callback receives **`Vortex\Database\Connection`**; type-hint it when you want (`Connection $db`).

> **Example — injected `Connection`**

```php
$this->db->transaction(function (\Vortex\Database\Connection $db) use ($postId, $author, $body): void {
    PostComment::createForPost($postId, $author, $body);
    $db->execute('UPDATE posts SET updated_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $postId]);
});
```

> **Example — manual control via `DB`**

```php
use Vortex\Database\DB;

DB::beginTransaction();
try {
    DB::execute('UPDATE posts SET published_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $id]);
    DB::commit();
} catch (\Throwable $e) {
    if (DB::inTransaction()) {
        DB::rollBack();
    }
    throw $e;
}
```

> **Example — script**

```php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

\Vortex\Database\DB::transaction(
    fn (\Vortex\Database\Connection $db) => $db->execute('UPDATE users SET name = ? WHERE id = ?', ['Pat', 1]),
);
```

## Related

- [Models](models.md) — querying, **`create`**, **`paginate`**
- [Cache](cache.md) — **`BlogHandler`**-style **`remember`** / invalidation; [Framework: Cache](../framework/cache.md) for config
- [Console](../framework/console.md) — **`migrate`** command details
- [Handlers](handlers.md) — **`DB`** / **`Cache`** static facades in handlers
