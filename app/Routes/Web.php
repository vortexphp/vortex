<?php

declare(strict_types=1);

use App\Handlers\AccountHandler;
use App\Handlers\Auth\LoginHandler;
use App\Handlers\BlogHandler;
use App\Handlers\BlogManageHandler;
use App\Handlers\Auth\LogoutHandler;
use App\Handlers\Auth\RegisterHandler;
use App\Handlers\DocsHandler;
use App\Handlers\HomeHandler;
use App\Middleware\GuestOnly;
use App\Middleware\RequireAuth;
use Vortex\Http\Response;
use Vortex\Routing\Route;

/**
 * HTTP route registration. Loaded automatically from `app/Routes/` (see {@see \Vortex\Routing\RouteDiscovery}).
 */
return static function (): void {
    Route::get('/', [HomeHandler::class, 'index'])
        ->get('/health', static fn (): Response => Response::json(['ok' => true]));

    Route::get('/docs', [DocsHandler::class, 'index'])
        ->get('/docs/{path...}', [DocsHandler::class, 'show']);

    Route::get('/blog', [BlogHandler::class, 'index']);
    Route::get('/blog/manage', [BlogManageHandler::class, 'index'], [RequireAuth::class]);
    Route::get('/blog/manage/posts/new', [BlogManageHandler::class, 'create'], [RequireAuth::class]);
    Route::post('/blog/manage/posts', [BlogManageHandler::class, 'store'], [RequireAuth::class]);
    Route::get('/blog/manage/posts/{id}/edit', [BlogManageHandler::class, 'edit'], [RequireAuth::class]);
    Route::post('/blog/manage/posts/{id}', [BlogManageHandler::class, 'update'], [RequireAuth::class]);
    Route::post('/blog/manage/posts/{id}/delete', [BlogManageHandler::class, 'destroy'], [RequireAuth::class]);
    Route::post('/blog/{slug}/comments', [BlogHandler::class, 'storeComment']);
    Route::get('/blog/{slug}', [BlogHandler::class, 'show']);

    Route::get('/register', [RegisterHandler::class, 'show'], [GuestOnly::class])
        ->post('/register', [RegisterHandler::class, 'store'], [GuestOnly::class]);

    Route::get('/login', [LoginHandler::class, 'show'], [GuestOnly::class])
        ->post('/login', [LoginHandler::class, 'store'], [GuestOnly::class]);

    Route::post('/logout', [LogoutHandler::class, 'store']);

    Route::get('/account', [AccountHandler::class, 'index'], [RequireAuth::class]);
    Route::get('/account/edit', [AccountHandler::class, 'edit'], [RequireAuth::class])
        ->post('/account/edit', [AccountHandler::class, 'update'], [RequireAuth::class]);
};
