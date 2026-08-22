<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WeddingQrAccessController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $wedding = Wedding::query()
            ->where('guest_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $request->session()->put("wedding_access.{$wedding->id}", true);
        $request->session()->regenerateToken();

        return redirect()
            ->route('weddings.show', $wedding)
            ->with('success', 'Willkommen! Der QR-Code hat die Galerie direkt geöffnet.');
    }
}
