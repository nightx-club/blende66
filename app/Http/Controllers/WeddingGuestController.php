<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class WeddingGuestController extends Controller
{
    public function show(Request $request, Wedding $wedding): View
    {
        abort_unless($wedding->is_active, 404);
        $unlocked = (bool) $request->session()->get("wedding_access.{$wedding->id}", false);
        $media = $unlocked ? $wedding->media()->where('is_published', true)->latest()->get() : collect();
        $albums = $media
            ->groupBy(fn ($item) => mb_strtolower(trim($item->guest_name ?: 'Ein lieber Gast')))
            ->map(fn ($items) => (object) [
                'name' => $items->first()->guest_name ?: 'Ein lieber Gast',
                'media' => $items,
                'photos' => $items->where('type', 'photo')->count(),
                'videos' => $items->where('type', 'video')->count(),
            ])
            ->values();

        return view('guest.wedding-portal', compact('wedding', 'unlocked', 'media', 'albums'));
    }

    public function unlock(Request $request, Wedding $wedding): RedirectResponse
    {
        abort_unless($wedding->is_active, 404);
        $validated = $request->validate(['pin' => ['required', 'string', 'min:4', 'max:20']]);
        if (! Hash::check($validated['pin'], $wedding->pin_hash)) {
            return back()->withErrors(['pin' => 'Diese PIN ist leider nicht richtig.'])->withInput();
        }
        $request->session()->put("wedding_access.{$wedding->id}", true);
        $request->session()->regenerateToken();

        return redirect()->route('weddings.show', $wedding)->with('success', 'Willkommen! Ihr könnt jetzt alle Momente entdecken.');
    }
}
