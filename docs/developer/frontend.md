# Frontend (CSS / Tailwind)

UI classes come from **Tailwind CSS v4**. Source input is **`assets/css/app.css`**; the **built** file served by the browser is **`public/css/app.css`**.

## Content sources

**`assets/css/app.css`** declares where Tailwind should scan for class names:

- **`../views/**/*.twig`**
- **`../../app/**/*.php`**
- **`../../lang/**/*.php`**

If you add new template paths or a JS layer that emits class names, extend **`@source`** so utilities are not purged.

> **Example — watch while developing**

```bash
npm run dev:css
```

> **Example — production build**

```bash
npm run build:css
```

Composer also exposes **`composer run build:css`** (delegates to npm).

## Typography

**`@tailwindcss/typography`** is enabled as a plugin. Use **`prose`** (and variants like **`prose-zinc`**, **`dark:prose-invert`**) on article wrappers — see docs layout and blog templates.

## Fonts

**Outfit** is loaded in **`app.css`** via Google Fonts; **`@theme`** sets **`--font-sans`**.

## Docs code blocks

Extra **`@layer`** rules in **`app.css`** style **highlight.js** inside **`article.docs-markdown`** so `/docs` previews look consistent.

## Related

- [Checklist](checklist.md) — reminder to rebuild CSS when adding new Tailwind classes
- [Views](../framework/views.md) — Twig stack
