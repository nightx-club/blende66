<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WeddingGuestController extends Controller
{
    public function show(Request $request, Wedding $wedding): View
    {
        abort_unless($wedding->is_active, 404);
        $unlocked = (bool) $request->session()->get("wedding_access.{$wedding->id}", false);
        $filter = Str::of($request->query('type', 'all'))->lower()->toString();
        if (! in_array($filter, ['all', 'photo', 'video'], true)) {
            $filter = 'all';
        }

        $mediaPage = null;
        $media = collect();
        $counts = ['all' => 0, 'photo' => 0, 'video' => 0];

        if ($unlocked) {
            $published = $wedding->media()->where('is_published', true);
            $counts = [
                'all' => (clone $published)->count(),
                'photo' => (clone $published)->where('type', 'photo')->count(),
                'video' => (clone $published)->where('type', 'video')->count(),
            ];
            $query = $wedding->media()->where('is_published', true);
            if ($filter !== 'all') {
                $query->where('type', $filter);
            }
            $mediaPage = $query->latest('created_at')->latest('id')->paginate(24)->withQueryString();
            $media = $mediaPage->getCollection();
        }

        return view('guest.gallery', compact('wedding', 'unlocked', 'media', 'mediaPage', 'counts', 'filter'));
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
