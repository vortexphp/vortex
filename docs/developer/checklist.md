# Checklist for a new feature

1. **Migration** (if new tables): add `database/migrations/YYYYMMDD_HHMMSS_description.php` returning a `Migration` class (`up`/`down`), then run **`php vortex migrate`**.
2. **Model**: `app/Models/Thing.php` — `extends Model`, **`$fillable`**, optional **`table()`**, static scopes returning **`QueryBuilder`**, **`paginate()`** for lists.
3. **Handler**: `app/Handlers/ThingHandler.php` — actions return **`Response`**, use **`View::html`**, **`Request::input`**, **`Validator::make`**, **`Csrf::validate`** on POSTs; inject **`ErrorRenderer`** for 404s if needed.
4. **Routes**: `app/Routes/Web.php` or a new file in **`app/Routes/`** (not `*Console.php`) — **`Route::get` / `post`**, middleware array as third argument when needed.
5. **Middleware** (optional): `app/Middleware/YourMiddleware.php`, register on the route or in **`config/app.php`** → **`middleware`**.
6. **Views**: `assets/views/things/index.twig` (dot name `things.index`). Run **`npm run build:css`** if you add new Tailwind classes in Twig or PHP strings.
7. **Strings**: mirror keys in **`lang/en.php`** and **`lang/bg.php`**; use **`trans('group.key')`** in PHP and Twig.

**Concrete references in this repo**: `BlogManageHandler` (CRUD + validation), `BlogHandler` (public + comment POST), `RequireAuth` / `GuestOnly` middleware, `Post` model (scopes + pagination).

More detail: [Project structure](project-structure.md), [Views](views.md), [Response](response.md), [Validation](validation.md), [Authentication](auth.md), [Database](database.md), [Cache](cache.md), [Events](events.md), [Mail](mail.md), [Frontend](frontend.md), [Testing](testing.md). Engine topics: [Framework docs](../framework/README.md).
