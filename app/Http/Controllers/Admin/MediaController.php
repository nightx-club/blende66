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
        $guestAlbums = $wedding->media()
            ->whereNotNull('guest_name')
            ->where('guest_name', '!=', '')
            ->selectRaw("MIN(guest_name) as name, COUNT(*) as media_count, SUM(CASE WHEN type = 'photo' THEN 1 ELSE 0 END) as photo_count, SUM(CASE WHEN type = 'video' THEN 1 ELSE 0 END) as video_count, SUM(file_size) as storage_bytes, MAX(created_at) as latest_upload_at")
            ->groupByRaw('LOWER(TRIM(guest_name))')
            ->orderByDesc('latest_upload_at')
            ->get()
            ->each(function ($album) use ($wedding): void {
                $album->name = mb_convert_case(mb_strtolower(trim($album->name)), MB_CASE_TITLE, 'UTF-8');
                $album->guest_email = $wedding->uploadSessions()
                    ->whereRaw('LOWER(TRIM(guest_name)) = ?', [mb_strtolower(trim($album->name))])
                    ->whereNotNull('guest_email')
                    ->latest()
                    ->value('guest_email');
            });
        $uploadSessions = $wedding->uploadSessions()
            ->whereHas('media')
            ->withCount(['media', 'media as photo_count_actual' => fn ($query) => $query->where('type', 'photo'), 'media as video_count_actual' => fn ($query) => $query->where('type', 'video')])
            ->withSum('media as storage_bytes', 'file_size')
            ->latest()
            ->get();
        $stats = ['photos' => $wedding->media()->where('type', 'photo')->count(), 'videos' => $wedding->media()->where('type', 'video')->count(), 'bytes' => (int) $wedding->media()->sum('file_size')];

        return view('admin.media.index', compact('wedding', 'media', 'guestAlbums', 'uploadSessions', 'stats'));
    }

    public function destroy(Wedding $wedding, Media $media, MediaCleaner $cleaner): RedirectResponse
    {
        $media = $wedding->media()->whereKey($media->id)->firstOrFail();
        $cleaner->delete($media);

        return back()->with('success', 'Medium wurde gelöscht.');
    }

    public function destroyGuestAlbum(Request $request, Wedding $wedding, MediaCleaner $cleaner): RedirectResponse
    {
        $data = $request->validate(['guest_name' => ['required', 'string', 'max:80']]);
        $guestKey = mb_strtolower(trim($data['guest_name']));
        $items = $wedding->media()
            ->whereRaw('LOWER(TRIM(guest_name)) = ?', [$guestKey])
            ->get();

        abort_if($items->isEmpty(), 404);

        $uploadSessionIds = $items->pluck('upload_session_id')->filter()->unique();
        $items->each(fn (Media $media) => $cleaner->delete($media));
        $wedding->uploadSessions()
            ->whereIn('id', $uploadSessionIds)
            ->whereDoesntHave('media')
            ->delete();

        return back()->with('success', "Das Album von {$data['guest_name']} mit {$items->count()} Dateien wurde dauerhaft gelöscht.");
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
