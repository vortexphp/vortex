<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Vortex\Contracts\Middleware;
use Vortex\Http\Request;
use Vortex\Http\Response;
use Vortex\Support\Benchmark;
use Vortex\View\View;

/**
 * Shares {@see $renderTimeMs} for the layout footer (same approach as the forum app’s auth middleware).
 */
final class ShareRenderTime implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        View::share('renderTimeMs', Benchmark::has('request') ? Benchmark::elapsedMs('request', 2) : 0.0);

        return $next($request);
    }
}
