# Developer guide

> **How to read these pages**  
> Examples use **callout blocks** (green sidebar when viewed under **`/docs`**) and **fenced code** with language tags for syntax highlighting.

How to build features in this project. Real references: **`app/Handlers/`**, **`app/Models/`**, **`app/Middleware/`** (blog, auth, account, docs preview).

## Core flow

- [Project structure](project-structure.md) — where code and assets live
- [Routes](routes.md) — registering HTTP endpoints
- [Handlers](handlers.md) — controllers: request → response
- [Views](views.md) — Twig templates, **`View::html`**, layouts, shared data
- [Response](response.md) — HTML, JSON, redirects, headers
- [Validation](validation.md) — **`Validator::make`**, errors, flash + redirect
- [Middleware](middleware.md) — global and per-route pipeline
- [Models](models.md) — database access with **`Model`** / **`QueryBuilder`**

## Cross-cutting

- [Authentication](auth.md) — sessions, **`RequireAuth`**, **`GuestOnly`**, **`ShareAuthUser`**
- [Database](database.md) — class migrations, **`php vortex migrate`**, **`php vortex migrate:down`**
- [Cache](cache.md) — real examples (**`BlogHandler`**, invalidation); [Framework: Cache](../framework/cache.md) for config
- [Events](events.md) — **`EventBus`**, listeners; [Framework: Events](../framework/events.md) for config
- [Mail](mail.md) — **`Mail::send`**, **`MailMessage`**; [Framework: Mail](../framework/mail.md) for drivers
- [Frontend (CSS)](frontend.md) — Tailwind, **`npm run build:css`**, content sources
- [Testing](testing.md) — PHPUnit, **`composer test`**

## Checklist and tooling

- [Checklist for a new feature](checklist.md) — ordered steps for a typical CRUD-style addition
- [In-app docs preview](docs-site.md) — **`/docs`** Markdown browser (debug only)
- [Files and uploads](../framework/files-and-uploads.md) — **`Storage`**, **`LocalPublicStorage`**, specs

## Engine and deploy

- [Framework (engine)](../framework/README.md) — HTTP kernel, config, views, validation, console, crypto, …
- [Production / deploy](../PRODUCTION.md)
