# Files and uploads

## `UploadedFile`

**`Vortex\Http\UploadedFile`** wraps one `$_FILES` entry.

| Method | Purpose |
|--------|---------|
| `wasAttempted()` | Client sent a file field (not `UPLOAD_ERR_NO_FILE`) |
| `hasFile()` | Attempted and non-empty original name |
| `isValid()` | `UPLOAD_ERR_OK` and `is_uploaded_file` |
| `clientErrorMessage()` | Translation **key** for upload errors (`upload.too_large`, etc.) |
| `mimeFromContent()` | MIME from temp file via `finfo` (do not trust client `Content-Type`) |
| `moveTo($absolutePath)` | Move to destination (creates parent dirs) |

Obtain via **`Request::file('input_name')`**.

> **Example — guard before storing**

```php
use Vortex\Http\Request;

$file = Request::file('photo');
if ($file === null || ! $file->hasFile() || ! $file->isValid()) {
    // flash error or return Response::redirect(...)
}
```

## `LocalPublicStorage`

Stores files under **`public/`** and returns a **relative path** suitable for URLs (`/uploads/...` via **`public_url()`**).

**`LocalPublicStorage::storeUpload($file, $directoryRelativeToPublic, $filenameStem, $mimeToExtension, $maxBytes)`**

- Validates the upload, size, and MIME against **`$mimeToExtension`** (map of allowed MIME → extension without dot).
- Rejects empty/`..` directory or invalid stem.
- Writes **`{directory}/{stem}.{ext}`** under `public/`.

**`LocalPublicStorage::deleteIfExists(?string $relativePath)`** — deletes a file under `public/` if it resolves safely.

The storage singleton is configured in bootstrap with the project’s **`public`** directory.

> **Example — store with an allow-list map**

```php
use Vortex\Files\LocalPublicStorage;
use Vortex\Http\Request;

$file = Request::file('document');
// … validate $file …

$relative = LocalPublicStorage::storeUpload(
    $file,
    'uploads/docs',
    'report-' . bin2hex(random_bytes(6)),
    [
        'application/pdf' => 'pdf',
        'text/plain' => 'txt',
    ],
    5_242_880, // 5 MiB
);
// $relative e.g. uploads/docs/report-a1b2c3.pdf → URL public_url($relative)
```

## Config

**`config/files.php`**: **`max_upload_bytes`** (env **`UPLOAD_MAX_BYTES`**), and nested **`avatar`** (`directory`, `mime_extensions`) for profile photos.

## App pattern: upload spec

Encapsulate allowed MIME map, directory, and max size in a small readonly class that reads **`Repository::get('files.*')`**, e.g. **`App\Uploads\AvatarUploadSpec::fromConfig()`**, then pass its fields into **`storeUpload`**. See [Uploads](../developer/uploads.md).
