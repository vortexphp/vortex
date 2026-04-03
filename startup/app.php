<?php

declare(strict_types=1);

use Vortex\Application;
use Vortex\Container;
use Vortex\Http\Csrf;
use Vortex\View\View;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

return Application::boot($basePath, static function (Container $container, string $basePath): void {
    View::share('csrfToken', Csrf::token());
})->container();
