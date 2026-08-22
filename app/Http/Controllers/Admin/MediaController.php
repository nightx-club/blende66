<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\UploadSession;
use App\Models\Wedding;
use App\Services\MediaCleaner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class MediaController extends Controller
{
    public function index(Wedding $wedding): View
    {
        $media = $wedding->media()->latest()->paginate(36);
        $uploadSessions = $wedding->uploadSessions()
            ->whereHas('media')
            ->withCount(['media', 'media as photo_count_actual' => fn ($query) => $query->where('type', 'photo'), 'media as video_count_actual' => fn ($query) => $query->where('type', 'video')])
            ->withSum('media as storage_bytes', 'file_size')
            ->latest()
            ->get();
        $stats = ['photos' => $wedding->media()->where('type', 'photo')->count(), 'videos' => $wedding->media()->where('type', 'video')->count(), 'bytes' => (int) $wedding->media()->sum('file_size')];

        return view('admin.media.index', compact('wedding', 'media', 'uploadSessions', 'stats'));
    }

    public function destroy(Wedding $wedding, Media $media, MediaCleaner $cleaner): RedirectResponse
    {
        $media = $wedding->media()->whereKey($media->id)->firstOrFail();
        $cleaner->delete($media);

        return back()->with('success', 'Medium wurde gelöscht.');
    }

    public function destroyUpload(Wedding $wedding, UploadSession $uploadSession, MediaCleaner $cleaner): RedirectResponse
    {
        $uploadSession = $wedding->uploadSessions()->whereKey($uploadSession->id)->firstOrFail();
        $items = $uploadSession->media()->get();
        $items->each(fn (Media $media) => $cleaner->delete($media));
        $uploadSession->delete();

        return back()->with('success', $items->count().' Dateien dieses Gast-Uploads wurden dauerhaft gelöscht.');
    }

    public function bulk(Request $request, Wedding $wedding, MediaCleaner $cleaner): RedirectResponse
    {
        $data = $request->validate(['media_ids' => ['required', 'array', 'max:200'], 'media_ids.*' => ['integer'], 'action' => ['required', 'in:publish,hide,delete']]);
        $items = $wedding->media()->whereIn('id', $data['media_ids'])->get();
        if ($data['action'] === 'delete') {
            $items->each(fn (Media $media) => $cleaner->delete($media));
        } else {
            $wedding->media()->whereIn('id', $items->pluck('id'))->update(['is_published' => $data['action'] === 'publish']);
        }

        return back()->with('success', $items->count().' Medien wurden aktualisiert.');
    }

    public function zip(Wedding $wedding): BinaryFileResponse
    {
        $temporary = tempnam(sys_get_temp_dir(), 'wedding-zip-');
        $archive = new ZipArchive;
        abort_unless($temporary && $archive->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'ZIP konnte nicht erstellt werden.');
        foreach ($wedding->media()->oldest()->get() as $index => $media) {
            if (Storage::disk('local')->exists($media->original_path)) {
                $archive->addFile(Storage::disk('local')->path($media->original_path), sprintf('%04d-%s', $index + 1, basename($media->original_name)));
            }
        }
        $archive->close();

        return response()->download($temporary, $wedding->slug.'-originale.zip')->deleteFileAfterSend(true);
    }
}
