# Validation

Use **`Vortex\Validation\Validator::make()`** in handlers on POST/PATCH bodies (and optionally on GET query if needed). On failure, flash **`errors`** (and usually **`old`**) and **redirect** back to the form GET route.

## Basic shape

> **Example — rules + custom messages**

```php
use Vortex\Http\Request;
use Vortex\Http\Response;
use Vortex\Http\Session;
use Vortex\Validation\Validator;

$data = [
    'title' => trim((string) Request::input('title', '')),
];

$validation = Validator::make(
    $data,
    ['title' => 'required|string|max:200'],
    ['title.required' => trans('things.validation.title_required')],
);

if ($validation->failed()) {
    Session::flash('errors', $validation->errors());
    Session::flash('old', $data);

    return Response::redirect('/things/new', 302);
}
```

## Rules

Supported rules are listed in [Framework: Validation](../framework/validation.md) (**`required`**, **`nullable`**, **`string`**, **`email`**, **`min:n`**, **`max:n`**, **`confirmed`**, …).

## Showing errors in Twig

Read flash **`errors`** (array of field → message) and **`old`** on the form page — same pattern as **`LoginHandler`** / **`BlogManageHandler`**. Hidden **`_csrf`** should use **`csrfToken`** from **`ShareAuthUser`**.

## CSRF first

Validate **`Csrf::validate()`** before reading trusted field data for persistence; on failure flash a single **`_form`** error (see [Handlers](handlers.md)).

## Related

- [Handlers](handlers.md) — full POST example with CSRF + validation  
- [Framework: Validation](../framework/validation.md) — rule reference and **`ValidationResult`** API  
