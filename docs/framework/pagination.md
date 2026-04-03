# Pagination

**`Vortex\Database\QueryBuilder::paginate($page, $perPage)`** returns **`Vortex\Pagination\Paginator`**.

## Properties (Twig-friendly)

- **`items`** — current page rows (`list` of models)
- **`total`**, **`page`**, **`per_page`**, **`last_page`**

## Links

Set the list URL (often a named route) with **`withBasePath()`**, then build page URLs with **`urlForPage(int $page)`** (clamped to **`1 … last_page`**). Existing query parameters on the base path are preserved; the page parameter is merged (default name **`page`**).

```php
$pagination = Post::query()
    ->orderByDesc('id')
    ->paginate($page, 15)
    ->withBasePath(route('blog.manage.index'));
```

Twig example:

```twig
{% if pagination.hasPages() %}
  <a href="{{ pagination.urlForPage(pagination.page - 1) }}"{% if pagination.onFirstPage() %} aria-disabled="true"{% endif %}>Prev</a>
  <a href="{{ pagination.urlForPage(pagination.page + 1) }}"{% if pagination.onLastPage() %} aria-disabled="true"{% endif %}>Next</a>
{% endif %}
```

## Helpers

- **`hasPages()`** — more than one page
- **`onFirstPage()`** / **`onLastPage()`**

See also [Models](../developer/models.md) (pagination section).
