<?php

declare(strict_types=1);

use Vortex\Application;
use Vortex\Config\Repository;
use Vortex\Container;
use Vortex\View\View;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

return Application::boot($basePath, static function (Container $container, string $basePath): void {
    $container->singleton(App\Docs\DocsIndex::class, static fn (): App\Docs\DocsIndex => new App\Docs\DocsIndex($basePath . '/docs'));
    $container->singleton(App\Docs\MarkdownRenderer::class, static fn (): App\Docs\MarkdownRenderer => new App\Docs\MarkdownRenderer());
    View::share('docsPreviewEnabled', (bool) Repository::get('app.debug', false));
})->container();
