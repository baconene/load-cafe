<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LogoController extends Controller
{
    private const PATH      = 'logo/current.png';
    private const TEXT_PATH = 'logo/text.txt';

    private static function storedText(): string
    {
        return Storage::disk('local')->exists(self::TEXT_PATH)
            ? trim(Storage::disk('local')->get(self::TEXT_PATH))
            : '';
    }

    public function edit(): Response
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        return Inertia::render('settings/Logo', [
            'currentLogoUrl' => Storage::disk('public')->exists(self::PATH)
                ? Storage::disk('public')->url(self::PATH)
                : null,
            'currentLogoText' => self::storedText(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $request->validate([
            'logo' => 'required|image|max:2048|mimes:png,jpg,jpeg,webp',
        ]);

        Storage::disk('public')->delete(self::PATH);
        $request->file('logo')->storeAs('logo', 'current.png', 'public');

        return back()->with('success', 'Logo updated successfully.');
    }

    public function updateText(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $request->validate([
            'logo_text' => 'required|string|max:60',
        ]);

        Storage::disk('local')->put(self::TEXT_PATH, trim($request->logo_text));

        return back()->with('success', 'Logo text updated successfully.');
    }

    public function resetText(): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        Storage::disk('local')->delete(self::TEXT_PATH);

        return back()->with('success', 'Logo text reset to default.');
    }

    public function destroy(): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        Storage::disk('public')->delete(self::PATH);

        return back()->with('success', 'Logo reset to default.');
    }
}
