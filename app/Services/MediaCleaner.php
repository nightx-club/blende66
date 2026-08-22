<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaCleaner
{
    public function delete(Media $media): void
    {
        Storage::disk('local')->delete(array_filter([$media->original_path, $media->gallery_path, $media->thumbnail_path]));
        $media->delete();
    }
}
