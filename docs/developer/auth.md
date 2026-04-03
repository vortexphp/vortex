# Authentication

This app uses **session-based** sign-in: the user id is stored in the PHP session; **`Password`** hashes verify credentials.

## Session keys

- **`auth_user_id`** — integer user id when signed in. **`Session::authUserId()`** returns it normalized, or **`null`**.
- **`ShareAuthUser`** clears a stale **`auth_user_id`** if the user row no longer exists.

> **Example — sign in after password check**  
> From **`LoginHandler::store`**: regenerate session, then store id.

```php
use Vortex\Http\Session;

Session::regenerate();
Session::put('auth_user_id', (int) $user->id);
```

> **Example — sign out**  
> From **`LogoutHandler`**: validate CSRF, then flush auth (clears id + regenerates session).

```php
use Vortex\Http\Session;

Session::flushAuth();
```

## Middleware

| Class | Use |
|-------|-----|
| **`RequireAuth`** | Redirects to **`/login?next=<current path>`** if **`Session::authUserId()`** is null. Put on routes that must be signed in. |
| **`GuestOnly`** | Redirects to **`/`** if already signed in. Use on **`/login`** and **`/register`**. |
| **`ShareAuthUser`** | Global (in **`config/app.php`** → **`middleware`**): loads **`User`** for **`auth_user_id`**, **`View::share('authUser', …)`**, **`View::share('csrfToken', …)`**. |

Register **`/login`** / **`/register`** with **`GuestOnly`** in the third argument to **`Route::get` / `post`**. Protect account or admin routes with **`[RequireAuth::class]`**.

## Passwords

Use **`Vortex\Crypto\Password::hash`** on register / password change and **`Password::verify`** on login. See [Crypto](../framework/crypto.md).

## Safe redirects after login

**`LoginHandler::safeNext`** only allows same-origin paths (leading **`/`**, not **`//`**). Reuse the same idea anywhere you take a **`next`** query param to avoid open redirects.

## References in this repo

- **`app/Handlers/Auth/LoginHandler`**, **`RegisterHandler`**, **`LogoutHandler`**
- **`app/Middleware/RequireAuth`**, **`GuestOnly`**, **`ShareAuthUser`**
- **`app/Routes/Web.php`** — which routes use which middleware
