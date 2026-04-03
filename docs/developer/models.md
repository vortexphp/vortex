# Models

## Base class

Extend **`Vortex\Database\Model`**. Place classes in **`app/Models/`**.

## Table name

`Model::table()` defaults to **snake_case plural** of the class basename (`Post` → **`posts`**, `BlogComment` → **`blog_comments`**). If your SQL table differs, override in the model:

```php
public static function table(): string
{
    return 'custom_table';
}
```

## Mass assignment

Declare **`protected static array $fillable = ['col1', 'col2'];`** for **`create()`**, **`save()`**, and **`update()`**-style flows. Columns not listed are ignored.

## Timestamps

**`protected static bool $timestamps = true`** (default) sets **`created_at`** / **`updated_at`** on create. Set to **`false`** for tables without those columns.

## Querying

```php
Item::query()
    ->where('user_id', $userId)
    ->orderByDesc('id')
    ->limit(10)
    ->get();

$item = Item::find($id);
```

Add **static methods** on the model for reusable scopes (e.g. `Post::published()` returning a `QueryBuilder`).

**Same connection as `DB`**: **`Model::connection()`** resolves the singleton **`Connection`** (identical to **`DB::select`** / **`DB::transaction`**). See [Database](database.md) for transactions and the **`DB`** facade.

**Scope + chain** (from `App\Models\Post`):

```php
use Vortex\Database\QueryBuilder;

public static function published(): QueryBuilder
{
    $now = date('Y-m-d H:i:s');

    return static::query()
        ->whereNotNull('published_at')
        ->where('published_at', '<=', $now);
}

// Usage:
$latest = Post::published()->orderByDesc('published_at')->limit(10)->get();
```

**Pagination** (`QueryBuilder::paginate` returns `items`, `total`, `page`, `per_page`, `last_page`):

```php
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = Item::query()
    ->where('user_id', $userId)
    ->orderByDesc('id')
    ->paginate($page, 15);

foreach ($result['items'] as $item) {
    // …
}
```

**Delete**:

```php
$item = Item::find($id);
if ($item !== null) {
    $item->delete();
}

// Or by primary key without loading:
Item::deleteId((int) $id);
```

## Creating and updating

```php
$row = Item::create(['name' => 'A', 'user_id' => 1]);

$row->name = 'B';
$row->save();

$row->update(['name' => 'C']);

Item::updateRecord($id, ['name' => 'D']);
```

See **`engine/Database/Model.php`** for **`save()`**, instance **`update(array)`**, and static **`updateRecord(int $id, array)`**.

## Schema

Define tables in **`database/schema.sql`** and incremental patches in **`database/patches/*.sql`**. Apply with **`php vortex migrate`**.
