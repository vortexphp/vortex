<?php

declare(strict_types=1);

use Vortex\Support\Env;

$database = Env::get('DB_DATABASE');
if ($database === null || $database === '') {
    $database = dirname(__DIR__) . '/storage/database.sqlite';
}

return [
    'driver' => Env::get('DB_DRIVER', 'sqlite'),
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => Env::get('DB_PORT', '3306'),
    'database' => $database,
    'username' => Env::get('DB_USERNAME', ''),
    'password' => Env::get('DB_PASSWORD', ''),
];
