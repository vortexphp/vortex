# Validation

**`Vortex\Validation\Validator::make($data, $rules, $messages = [], $attributes = [])`** returns **`ValidationResult`**.

Rules are **pipe-separated** per field, e.g. **`title` => `'required|string|max:120'`**.

> **Example — rules + custom messages**

```php
use Vortex\Http\Request;
use Vortex\Http\Response;
use Vortex\Http\Session;
use Vortex\Validation\Validator;

$validation = Validator::make(
    [
        'email' => Request::input('email', ''),
        'password' => Request::input('password', ''),
        'password_confirmation' => Request::input('password_confirmation', ''),
    ],
    [
        'email' => 'required|email|max:255',
        'password' => 'required|string|min:8|confirmed',
    ],
    [
        'email.required' => trans('auth.validation.email_required'),
        'password.min' => trans('auth.validation.password_short'),
    ],
    [
        'email' => trans('forms.labels.email'),
        'password' => trans('forms.labels.password'),
    ],
);

if ($validation->failed()) {
    Session::flash('errors', $validation->errors());
    return Response::redirect('/register', 302);
}
```

## Supported rules

| Rule | Meaning |
|------|---------|
| **`required`** | Not empty (null, `''`, whitespace-only string) |
| **`nullable`** | If empty, skip other rules for this field |
| **`string`** | Must be null or string |
| **`email`** | Non-empty values must pass `FILTER_VALIDATE_EMAIL` |
| **`min:n`** | String byte length ≥ n (positive integer) |
| **`max:n`** | String byte length ≤ n |
| **`confirmed`** | Value must equal `$data[$field . '_confirmation']` (errors attach to `{field}_confirmation` when failing) |

Unknown rule names throw **`InvalidArgumentException`**.

## Messages

- Per field + rule: **`$messages['field.rule']`**
- Fallback per rule: **`$messages['rule']`**
- **`:attribute`** in templates uses **`$attributes[$field]`** or a humanized field name.

## Result API

**`ValidationResult`**: **`failed()`**, **`errors()`** (map field → first message), **`first($field)`**.

Typical POST flow: if **`failed()`**, flash **`errors()`** and **`old` input**, redirect back to the form.
