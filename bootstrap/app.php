<?php

declare(strict_types=1);

use Vortex\AppContext;
use Vortex\Cache\CacheFactory;
use Vortex\Config\Repository;
use Vortex\Container;
use Vortex\Contracts\Cache;
use Vortex\Contracts\Mailer;
use Vortex\Database\Connection;
use Vortex\Events\Dispatcher;
use Vortex\Events\DispatcherFactory;
use Vortex\Files\LocalPublicStorage;
use Vortex\Http\Csrf;
use Vortex\Http\ErrorRenderer;
use Vortex\Http\Session;
use Vortex\I18n\Translator;
use Vortex\Mail\MailFactory;
use Vortex\Routing\RouteDiscovery;
use Vortex\Routing\Router;
use Vortex\Support\Env;
use Vortex\View\Factory;
use Vortex\View\View;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

Env::load($basePath . '/.env');

$container = new Container();
$container->instance(Container::class, $container);

$container->singleton(Repository::class, static fn (): Repository => new Repository($basePath . '/config'));

Repository::setInstance($container->make(Repository::class));

$container->singleton(Connection::class, static fn (): Connection => new Connection());

$container->singleton(Cache::class, static fn (): Cache => CacheFactory::make($basePath));

$container->singleton(Dispatcher::class, static fn (Container $c): Dispatcher => DispatcherFactory::make($c));

$container->singleton(Mailer::class, static fn (): Mailer => MailFactory::make($basePath));

$container->singleton(Session::class, static fn (): Session => new Session());

Session::setInstance($container->make(Session::class));

$container->singleton(Csrf::class, static fn (): Csrf => new Csrf());

Csrf::setInstance($container->make(Csrf::class));

$container->singleton(LocalPublicStorage::class, static function () use ($basePath): LocalPublicStorage {
    return new LocalPublicStorage($basePath . '/public');
});

LocalPublicStorage::setInstance($container->make(LocalPublicStorage::class));

$container->singleton(Translator::class, static function () use ($basePath): Translator {
    $supported = Repository::get('app.locales', ['en', 'bg']);
    if (! is_array($supported)) {
        $supported = ['en', 'bg'];
    }
    /** @var list<string> $supported */
    $supported = array_values(array_filter(array_map(strval(...), $supported)));

    return new Translator(
        $basePath . '/lang',
        (string) Repository::get('app.locale', 'en'),
        (string) Repository::get('app.fallback_locale', 'en'),
        $supported,
    );
});

Translator::setInstance($container->make(Translator::class));

$container->singleton(Factory::class, static function () use ($basePath): Factory {
    $debug = (bool) Repository::get('app.debug', false);

    return new Factory(
        $basePath . '/assets/views',
        $debug,
        $debug ? null : $basePath . '/storage/cache/twig',
    );
});

View::useFactory($container->make(Factory::class));
View::share('appName', (string) Repository::get('app.name', 'App'));
View::share('docsPreviewEnabled', (bool) Repository::get('app.debug', false));

$container->singleton(App\Docs\DocsIndex::class, static fn (): App\Docs\DocsIndex => new App\Docs\DocsIndex($basePath . '/docs'));
$container->singleton(App\Docs\MarkdownRenderer::class, static fn (): App\Docs\MarkdownRenderer => new App\Docs\MarkdownRenderer());

$container->singleton(ErrorRenderer::class, static fn (): ErrorRenderer => new ErrorRenderer($basePath));

$container->singleton(Router::class, static function (Container $c) use ($basePath): Router {
    $router = new Router($c);
    RouteDiscovery::loadHttpRoutes($router, $basePath);

    return $router;
});

AppContext::set($container);

return $container;
