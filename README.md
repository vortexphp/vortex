# Vortex Startup App

Minimal startup-ready PHP app on `vortexphp/framework`.

## Requirements

- PHP `8.2+`
- Composer
- Node.js + npm (for Tailwind CSS build)

## Create Project

```bash
composer create-project vortexphp/vortex my-app
cd my-app
npm install
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

Build CSS once:

```bash
npm run build
```

Run the app:

```bash
php vortex serve
```

Open:

- `http://localhost:8000/`
- `http://localhost:8000/health`

## Useful Commands

```bash
composer run doctor
composer run smoke
composer run db-check
```

## Production Install

```bash
composer run install:prod
npm run build
```

