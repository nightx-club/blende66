<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\Request;
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

        return view('guest.gallery', compact('wedding', 'media', 'mediaPage', 'guestGalleries'));
    }
}
