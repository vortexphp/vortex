<?php

declare(strict_types=1);

namespace App\Controllers;

use Vortex\Http\Controller;
use Vortex\Http\Response;
use Vortex\View\View;

final class HomeController extends Controller
{
    public function index(): Response
    {
        return View::html('home', [
            'title' => \trans('home.title'),
        ]);
    }
}
