<?php

declare(strict_types=1);

use Vortex\Support\Env;

return [
    'max_upload_bytes' => (int) Env::get('UPLOAD_MAX_BYTES', '2097152')
];
