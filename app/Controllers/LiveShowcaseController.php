<?php

declare(strict_types=1);

namespace App\Controllers;

use Vortex\Http\Controller;
use Vortex\Http\Response;
use Vortex\View\View;

final class LiveShowcaseController extends Controller
{
    public function index(): Response
    {
        return View::html('live.showcase', [
            'title' => \trans('live_showcase.title'),
            'metaDescription' => \trans('live_showcase.meta_description'),
        ]);
    }
}
