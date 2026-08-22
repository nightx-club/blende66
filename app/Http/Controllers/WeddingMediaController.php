<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WeddingMediaController extends Controller
{
    public function cover(Wedding $wedding): BinaryFileResponse
    {
        abort_unless(($wedding->is_active || auth()->user()?->is_admin) && $wedding->cover_image_path && Storage::disk('local')->exists($wedding->cover_image_path), 404);

        return response()->file(Storage::disk('local')->path($wedding->cover_image_path), ['Content-Type' => 'image/webp', 'Cache-Control' => 'public, max-age=86400']);
    }

    public function view(Request $request, Wedding $wedding, Media $media): BinaryFileResponse
    {
        $media = $this->scopedMedia($wedding, $media);
        $path = $media->type === 'photo' ? $media->gallery_path : $media->original_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => $media->type === 'photo' ? 'image/webp' : $media->mime_type,
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function thumbnail(Wedding $wedding, Media $media): BinaryFileResponse
    {
        $media = $this->scopedMedia($wedding, $media);
        abort_unless($media->thumbnail_path && Storage::disk('local')->exists($media->thumbnail_path), 404);

        return response()->file(Storage::disk('local')->path($media->thumbnail_path), ['Content-Type' => 'image/webp', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function download(Wedding $wedding, Media $media): BinaryFileResponse
    {
        $media = $this->scopedMedia($wedding, $media);
        abort_unless(Storage::disk('local')->exists($media->original_path), 404);

        return response()->download(Storage::disk('local')->path($media->original_path), $media->original_name, ['X-Content-Type-Options' => 'nosniff']);
    }

    private function scopedMedia(Wedding $wedding, Media $media): Media
    {
        $query = $wedding->media()->whereKey($media->id);
        if (! auth()->user()?->is_admin) {
            $query->where('is_published', true);
        }

        return $query->firstOrFail();
    }
}
