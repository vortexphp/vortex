# Crypto

## Passwords

**`Vortex\Crypto\Password`** — use for **user credentials** and similar secrets at rest.

- **`Password::hash($plain)`** — `password_hash(..., PASSWORD_DEFAULT)` (bcrypt or Argon2id depending on PHP).
- **`Password::verify($plain, $hash)`** — `password_verify`.
- **`Password::needsRehash($hash)`** — call after verify to re-save when PHP’s default algorithm/cost changes.

Do **not** use `Crypt` for passwords.

> **Example — register and login**

```php
use Vortex\Crypto\Password;

$hash = Password::hash($plainPassword);
// … store $hash in DB …

if (Password::verify($plainPassword, $user->password_hash)) {
    if (Password::needsRehash($user->password_hash)) {
        $user->password_hash = Password::hash($plainPassword);
        $user->save();
    }
}
```

## Keyed hashing (`APP_KEY`)

**`Vortex\Crypto\Crypt`** — HMAC with the application secret from **`.env`**:

- Set **`APP_KEY`** (recommended: `base64:` + 32 random bytes).
- **`Crypt::hash($value)`** / **`Crypt::hmac($value, $algorithm)`** — hex MAC.
- **`Crypt::verify($value, $hexMac, $algorithm)`** — constant-time check.

Use for signed payloads, tamper detection, tokens — **not** for storing password hashes.

> **Example — sign and verify a payload string**

```php
use Vortex\Crypto\Crypt;

$payload = 'user=42&expires=' . time();
$mac = Crypt::hash($payload);

// Later:
if (! Crypt::verify($payload, $mac)) {
    // reject tampered request
}
```

## Reminders

**`Vortex\Crypto\SecurityHelp::namespaceGuide()`** returns short lines describing Password vs Crypt; **`php vortex doctor`** can surface this.
