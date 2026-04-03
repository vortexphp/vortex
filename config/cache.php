<?php

declare(strict_types=1);

use Vortex\Support\Env;

$path = Env::get('CACHE_PATH');
if ($path === null || trim((string) $path) === '') {
    $path = dirname(__DIR__) . '/storage/cache/data';
}

return [
    'driver' => strtolower(trim((string) Env::get('CACHE_DRIVER', 'file'))),
    'path' => $path,
    'prefix' => (string) Env::get('CACHE_PREFIX', 'vortex:'),
];
