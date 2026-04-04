<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;
use Vortex\Container;
use Vortex\Http\Response;

/**
 * Application-level exception hook. Return a {@see Response} to short-circuit the
 * default {@see \Vortex\Http\ErrorRenderer}; return null to let the framework render.
 */
final class Handler
{
    public function handle(Throwable $e, Container $container): ?Response
    {
        return null;
    }
}
