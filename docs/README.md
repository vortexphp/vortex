# Documentation

- **Developer guide** (structure, routes, handlers, views, response, validation, middleware, models, auth, DB, cache, events, mail, frontend, tests, checklist): [developer/README.md](developer/README.md)
- **Framework overview** (what lives in `engine/` and `app/`): [framework/README.md](framework/README.md)
- **Production / deploy**: [PRODUCTION.md](PRODUCTION.md)

With **`APP_DEBUG=1`**, the app serves a browsable Markdown preview at **`/docs`** (all `*.md` files under this `docs/` tree). Pages use **callout blockquotes** for example titles and **fenced code** with language tags so **highlight.js** can color PHP, Bash, Twig, HTML, and similar snippets.

Sidebar order and grouping come from **`menu.php`**: each block has a `title_key` (see `lang/*.php` under `docs.menu.*`) or a literal `title`, plus an `items` list of slugs without the `.md` suffix. Files that are not listed still show up under **Other**, sorted by title. Labels in the menu use each file’s first `#` heading when present.

The preview adds **anchors on `##`–`####` headings**, a **table of contents** (desktop sidebar + mobile disclosure), **previous/next** in menu order, a **Documentation home** link, and a **filter** field on the sidebar (`public/js/docs-nav.js`).
