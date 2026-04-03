# Mail

Engine API: [Framework: Mail](../framework/mail.md). Typical flow: build a **`MailMessage`**, then **`Mail::send`** (or **`$this->mailer->send`** from **`Mailer`**) from a handler or event listener after the main action succeeds.

> **Example — password reset (conceptual)**

```php
use Vortex\Mail\Mail;
use Vortex\Mail\MailMessage;

$link = $appUrl . '/reset?token=' . rawurlencode($token);
Mail::send(new MailMessage(
    Mail::defaultFrom(),
    [[$user->email, $user->name ?? '']],
    trans('auth.reset_subject'),
    trans('auth.reset_text', ['link' => $link]),
));
```

Use **`log`** driver locally to inspect **`storage/logs/mail.log`**. In production set **`MAIL_DRIVER=smtp`** (or **`native`** on a host with sendmail) and real **`MAIL_FROM_*`** / SMTP credentials.

## Related

- [Handlers](handlers.md) — **`Mail`** static facade
- [Events](events.md) — send from listeners
- [Framework: Mail](../framework/mail.md)
