# Framework (engine)

> **How to read these pages**  
> Green callout blocks introduce each **example**. Code samples use syntax highlighting (PHP, Bash, Twig, HTML, env) when you view them under **`/docs`** with debug on.

The HTTP stack lives under **`engine/`** (`Vortex\*`). The app wires it in **`bootstrap/app.php`**; HTTP entry is **`public/index.php`** → **`Kernel`**.

| Topic | File |
|--------|------|
| Bootstrap, container, facades | [Bootstrap and container](bootstrap-and-container.md) |
| `config/*.php`, env, `Repository` | [Configuration](configuration.md) |
| Kernel, request/response, session, CSRF, proxies | [HTTP](http.md) |
| Twig, `View`, shared data | [Views](views.md) |
| `Storage`, uploads, `public/` | [Files and uploads](files-and-uploads.md) |
| Locales, `trans()`, middleware | [Internationalization](i18n.md) |
| Passwords vs `APP_KEY` HMAC | [Crypto](crypto.md) |
| Small utilities (`*Help`, `Log`, `Env`) | [Support helpers](support-helpers.md) |
| `php power` CLI | [Console](console.md) |
| `Validator::make`, rules | [Validation](validation.md) |
| `Cache` contract, `Cache::remember`, file store | [Cache](cache.md) |
| `Paginator`, `QueryBuilder::paginate` | [Pagination](pagination.md) |
| `Dispatcher`, `EventBus::dispatch` | [Events](events.md) |
| `Mailer`, `Mail::send` | [Mail](mail.md) |
| Error pages, `Log::exception` | [Errors and logging](errors-and-logging.md) |

Routing, handlers, middleware, and models are covered in the [developer guide](../developer/README.md). Static facades: **`DB`** → [developer/database.md](../developer/database.md); **`Cache`** → [Cache](cache.md); **`EventBus`** → [Events](events.md); **`Mail`** → [Mail](mail.md).
