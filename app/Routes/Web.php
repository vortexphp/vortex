<?php

declare(strict_types=1);

use App\Handlers\HomeHandler;
use App\Http\Controllers\HomeController;
use Vortex\Http\Response;
use Vortex\Routing\Route;

/**
 * HTTP route registration. Loaded automatically from `app/Routes/` (see {@see \Vortex\Routing\RouteDiscovery}).
 */

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/health', static fn (): Response => Response::json(['ok' => true]))->name('health');
