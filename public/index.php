<?php

declare(strict_types=1);

/**
 * Front controller. HTTP routes: app/Routes/*.php (see RouteDiscovery in bootstrap/app.php).
 */

use Vortex\Http\Kernel;

$basePath = dirname(__DIR__);

try {
    /** @var \Vortex\Container $container */
    $container = require $basePath . '/bootstrap/app.php';
    (new Kernel($container))->send();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Application failed to start.\n";
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
}
