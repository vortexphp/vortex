<?php

declare(strict_types=1);

use Vortex\Http\Response;
use Vortex\Live\Http\LiveController;
use Vortex\Routing\Route;
use Vortex\vortex\app\Controllers\HomeController;
use Vortex\vortex\app\Controllers\LiveShowcaseController;

/**
 * HTTP route registration. Loaded automatically from `app/Routes/` (see {@see \Vortex\Routing\RouteDiscovery}).
 */

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/live', [LiveShowcaseController::class, 'index'])->name('live.showcase');
Route::get('/health', static fn (): Response => Response::json(['ok' => true]))->name('health');
Route::post('/live/message', [LiveController::class, 'message'])->name('live.message');
