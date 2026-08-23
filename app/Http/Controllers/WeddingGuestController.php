<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class WeddingGuestController extends Controller
{
    public function home(Request $request): View
    {
        return $this->show($request, Wedding::query()->where('slug', 'blende6')->firstOrFail());
    }

    public function show(Request $request, Wedding $wedding): View
    {
        abort_unless($wedding->is_active, 404);
        $unlocked = (bool) $request->session()->get("wedding_access.{$wedding->id}", false);

        $mediaPage = null;
        $media = collect();
        $guestGalleries = collect();

        if ($unlocked) {
            $guestGalleries = $wedding->media()
                ->where('is_published', true)
                ->whereNotNull('guest_name')
                ->where('guest_name', '!=', '')
                ->selectRaw('MIN(guest_name) as name, COUNT(*) as files_count')
                ->groupByRaw('LOWER(TRIM(guest_name))')
                ->orderBy('name')
                ->get();
            $query = $wedding->media()->where('is_published', true);
            $mediaPage = $query->latest('created_at')->latest('id')->paginate(24)->withQueryString();
            $media = $mediaPage->getCollection();
        }

        return view('guest.gallery', compact('wedding', 'unlocked', 'media', 'mediaPage', 'guestGalleries'));
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

        return redirect()->route('weddings.show', $wedding);
    }
}
