# Mail

**`Vortex\Contracts\Mailer`** sends **`Vortex\Mail\MailMessage`** instances. Bound in **`bootstrap/app.php`** via **`MailFactory::make`**. Static **`Vortex\Mail\Mail`** mirrors **`Mail::send`** and **`Mail::defaultFrom()`**.

## Drivers (`config/mail.php` / `.env`)

| `MAIL_DRIVER` | When to use |
|---------------|-------------|
| **`log`** (default) | Development: append to **`storage/logs/mail.log`**. |
| **`null`** | Tests or “mail disabled”. |
| **`native`** | Host has a working MTA; uses PHP **`mail()`**. |
| **`smtp`** | Submission to **`MAIL_HOST`** (port **587** + STARTTLS, or **465** + **`MAIL_ENCRYPTION=ssl`**). **`ext-openssl`** required for TLS. AUTH PLAIN when **`MAIL_USERNAME`** is set. |

Keys: **`mail.from.address`**, **`mail.from.name`**, **`mail.smtp.*`**.

> **Example — send**

```php
use Vortex\Mail\Mail;
use Vortex\Mail\MailMessage;

Mail::send(new MailMessage(
    Mail::defaultFrom(),
    [['user@example.com', 'User']],
    trans('auth.reset_subject'),
    $textBody,
    $htmlBodyOptional,
));
```

Inject **`Mailer`** in listeners or handlers when you prefer not to use the facade.

## Related

- [Configuration](configuration.md)
- [Mail](../developer/mail.md)
