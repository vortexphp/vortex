# Database

The app uses **`Vortex\Database\DatabaseManager`** and a default **`Vortex\Database\Connection`** (PDO) from **`config/database.php`** (**`default`** + **`connections`** with per-connection **`driver`**). For static calls after bootstrap, use **`Vortex\Database\DB`** (default connection: **`DB::select`**, **`DB::execute`**, **`DB::transaction`**, …). Use **`DB::connection('name')`** for another configured connection. Models extend **`Model`** and use the **default** connection only — see [Models](models.md).

## Schema layout

| Artifact | Purpose |
|----------|---------|
| **`database/migrations/*.php`** | Class migrations. Each file returns a **`Vortex\Database\Schema\Migration`** with **`id()`**, **`up()`**, and **`down()`**. |

Use sortable IDs (timestamp + name), e.g. **`20260403_120000_add_posts_table`**.

## Applying changes

```bash
php vortex migrate
php vortex migrate:down
```

`migrate` runs all pending migration classes and records them in **`vortex_migrations`**. `migrate:down` rolls back the last applied batch using each migration’s **`down()`** method.

> **Example — schema builder columns**

```php
use Vortex\Database\Connection;
use Vortex\Database\Schema\Migration;
use Vortex\Database\Schema\Schema;

return new class implements Migration {
    public function id(): string { return '20260403_120000_create_users'; }

    public function up(Connection $db): void
    {
        Schema::connection($db)->create('users', static function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->text('avatar')->nullable();
            $table->timestamps();
        });
    }

    public function down(Connection $db): void
    {
        Schema::connection($db)->dropIfExists('users');
    }
};
```

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
