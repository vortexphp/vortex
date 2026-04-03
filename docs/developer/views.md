# Views

Twig templates live under **`assets/views/`**. Handlers return pages with **`View::html('dot.name', $data)`**.

## Naming

- **`blog.show`** → **`assets/views/blog/show.twig`**
- Layouts: e.g. **`layouts/default.twig`**, included with **`{% extends 'layouts/default.twig' %}`** and **`{% block content %}…{% endblock %}`**.

> **Example — handler renders a page**

```php
use Vortex\Http\Response;
use Vortex\View\View;

return View::html('account.index', [
    'title' => trans('account.title'),
    'user' => $user,
]);
```

## Shared data

Global middleware and bootstrap call **`View::share('key', $value)`**. This project shares **`authUser`**, **`csrfToken`**, **`appName`**, etc., so every template can use them without the handler passing them again.

From a handler you only pass **page-specific** keys in the second argument to **`View::html`**.

## Twig helpers

**`trans`**, **`public_url`**, **`url_query`**, **`session_flash`**, filters like **`excerpt_html`** — see [Framework: Views](../framework/views.md).

## Errors

**`ErrorRenderer`** uses **`View::render('errors.404', …)`** (string HTML), not **`View::html`**, before wrapping in **`Response::html`**.

## CSS and classes

Tailwind scans Twig files listed in **`assets/css/app.css`**. After adding new utility classes in templates, run **`npm run build:css`**. See [Frontend (CSS)](frontend.md).

## Related

- [Handlers](handlers.md) — typical handler + view flow  
- [Framework: Views](../framework/views.md) — factory, cache, full Twig extension table  
