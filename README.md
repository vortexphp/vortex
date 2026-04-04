# Vortex Startup App

Minimal startup-ready PHP app on `vortexphp/framework`.

The `vortex` file in the project root is the CLI entry point (also registered in Composer `bin`). Run it with `php vortex <command>` (for example `php vortex serve`).

## Requirements

- PHP `8.2+`
- Composer
- Node.js + npm (for Tailwind CSS build)
- GNU Make (optional; use the `composer run …` equivalents below if you prefer)
- Docker (optional; see [Docker](#docker))

## Monorepo development

`composer.json` includes **path** `repositories` so a full Vortex checkout can symlink `vendor/vortexphp/framework` and `vendor/vortexphp/live` to sibling packages while you work on the framework. That is optional: `vendor/` is gitignored, and installs from Packagist use normal package copies when those path entries are absent or unused. If you see symlinks under `vendor/vortexphp/`, you are using the local path repositories.

## Quick start (clone)

```bash
make setup          # composer install + npm install; copies .env.example → .env if .env is missing
# edit .env: set APP_KEY and APP_URL (see .env.example comments)
make build
make serve          # or: php vortex serve
```

Same without Make:

```bash
composer run setup
cp .env.example .env   # if you skipped `make setup`
composer run build
php vortex serve
```

## Create Project

```bash
composer create-project vortexphp/vortex my-app
cd my-app
composer run setup
cp .env.example .env
```

## Install (existing clone)

```bash
composer install
npm install
cp .env.example .env
```

Generate `APP_KEY` and put it in `.env`:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
```

Set at least:

- `APP_KEY`
- `APP_URL` (for local: `http://localhost:8000`)

## Run Locally

Build front-end assets (Tailwind → `public/css/app.css`, then copy Live → `public/js/live.js`):

```bash
make build
# or: composer run build
```

`npm run build` only compiles CSS; `composer run build` / `make build` runs that step plus `sync-live-assets` (implementation: `build/sync-live-assets.php`), so prefer Make or Composer for a full asset build.

Watch CSS during development:

```bash
make dev
# or: npm run dev / composer run dev
```

Run the app:

```bash
php vortex serve
# or: make serve
```

Open:

- `http://localhost:8000/`
- `http://localhost:8000/health`

## Useful Commands

```bash
make test              # or: composer run test
composer run doctor
composer run smoke
composer run db-check
```

## Assets

| Output | Source | How it is produced |
|--------|--------|--------------------|
| `public/css/app.css` | `ui/css/app.css` | Tailwind: `npm run build` / `npm run dev` |
| `public/js/live.js` | `vendor/vortexphp/live/resources/live.js` | `build/sync-live-assets.php` via `composer run sync-live-assets` (also part of `composer run build`; runs after `composer install` / `update`) |

Templates reference `/css/app.css` and `/js/live.js` only. There is no separate `public/dist/` pipeline in this app; that path is gitignored to avoid duplicate stale files.

## Database seeders

`database/seeders/DatabaseSeeder.php` (`Database\Seeders\DatabaseSeeder`) is the entry point for seed data. Wire it into your own commands or tooling as the project grows.

## Exceptions

- `App\Exceptions\AppException` — base class for domain-level errors.
- `App\Exceptions\Handler` — implement `handle()` to return a custom `Response` for specific throwables; return `null` to use the framework `ErrorRenderer` (wired from `public/index.php`).

## Docker

Requires `vendor/` (run `composer install` on the host first). Then:

```bash
docker compose up
```

Serves on `http://127.0.0.1:8080/` (PHP built-in server, `0.0.0.0:8080` inside the container).

## Production Install

```bash
composer run install:prod
composer run build
```

(`composer run build` runs `npm run build` and then syncs Live assets; Node is required for the Tailwind step.)

