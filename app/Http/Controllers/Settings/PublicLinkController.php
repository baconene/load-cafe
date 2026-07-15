<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Support\Brand;
use Inertia\Inertia;
use Inertia\Response;

class PublicLinkController extends Controller
{
    public function edit(): Response
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        return Inertia::render('settings/PublicLink', [
            'siteUrl'   => rtrim(config('app.url'), '/'),
            'brandName' => Brand::name(),
        ]);
    }
}
