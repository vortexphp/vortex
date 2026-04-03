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

## `Storage`

**`Vortex\Files\Storage`** is the façade over **named disks** from **`config/storage.php`** (see below). Call **`Storage::setBasePath($projectRoot)`** once at bootstrap (with **`Log::setBasePath`**). Disks are built **lazily** when first used.

### Disks and drivers

| Driver | Purpose |
|--------|---------|
| **`local`** | Directory under the project (default **`root`**: **`storage/app`**) — **`Filesystem`** only |
| **`local_public`** | **`public/`** tree — implements **`PublicFilesystem`** (upload validation via **`LocalPublicStorage`**) |
| **`null`** | No-op reads/writes (tests or “disable exports”) |

**`Storage::disk(?string $name)`** returns **`Vortex\Contracts\Filesystem`**. Cast or use **`instanceof PublicFilesystem`** when you need **`storeUpload`**.

### Config keys (`config/storage.php`)

| Key | Purpose |
|-----|---------|
| **`default`** | Disk used by **`Storage::put`**, **`get`**, **`exists`**, **`delete`**, **`append`** (env **`STORAGE_DISK`**) |
| **`public_disk`** | Disk name for **`Storage::publicRoot()`** and **`Storage::deletePublic()`** (must be **`local_public`**) |
| **`upload_disk`** | Disk name for **`Storage::storeUpload()`** (must be **`local_public`**) |
| **`disks.{name}.driver`** | **`local`**, **`local_public`**, or **`null`** |
| **`disks.{name}.root`** | For **`local`**: path segment relative to project root (default **`storage/app`**) |

### Facade shortcuts

| Method | Target |
|--------|--------|
| **`Storage::put` / `append` / `get` / `exists` / `delete`** | **`default`** disk |
| **`Storage::storeUpload(...)`** | **`upload_disk`** |
| **`Storage::publicRoot()`**, **`deletePublic()`** | **`public_disk`** |

> **Example — public upload**

```php
use Vortex\Files\Storage;
use Vortex\Http\Request;

$file = Request::file('document');
// … validate $file …

$relative = Storage::storeUpload(
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

> **Example — private export**

```php
use Vortex\Files\Storage;

Storage::put('exports/run-' . date('Ymd') . '.csv', $csvBody);
```

> **Example — named disk**

```php
use Vortex\Files\Storage;

Storage::disk('local')->put('cache/foo.json', $json);
$raw = Storage::disk('null')->get('any'); // always null
```

## `LocalPublicStorage`

Singleton used by the **`local_public`** driver for **`storeUpload`** validation and path rules. Register with **`LocalPublicStorage::setInstance()`** in **`bootstrap/app.php`**.

## Config

**`config/storage.php`** — disks and **`STORAGE_DISK`** (default disk).

**`config/files.php`**: **`max_upload_bytes`** (env **`UPLOAD_MAX_BYTES`**), and nested **`avatar`** (`directory`, `mime_extensions`) for profile photos.

## App pattern: upload spec

Encapsulate allowed MIME map, directory, and max size in a small readonly class that reads **`Repository::get('files.*')`**, e.g. **`App\Uploads\AvatarUploadSpec::fromConfig()`**, then pass its fields into **`storeUpload`**. See [Uploads](../developer/uploads.md).
