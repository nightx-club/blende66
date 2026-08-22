<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class WeddingArchiveController extends Controller
{
    public function all(Wedding $wedding): BinaryFileResponse
    {
        return $this->download(
            $wedding->media()->where('is_published', true)->oldest(),
            $wedding->slug.'-alle-erinnerungen.zip'
        );
    }

    public function guest(Request $request, Wedding $wedding): BinaryFileResponse
    {
        $data = $request->validate(['guest' => ['required', 'string', 'max:80']]);

        return $this->download(
            $wedding->media()->where('is_published', true)->whereRaw('LOWER(guest_name) = ?', [mb_strtolower($data['guest'])])->oldest(),
            $wedding->slug.'-'.Str::slug($data['guest']).'-album.zip'
        );
    }

    private function download(Builder|Relation $query, string $filename): BinaryFileResponse
    {
        $media = $query->get();
        abort_if($media->isEmpty(), 404, 'Dieses Album enthält noch keine Dateien.');

        $temporary = tempnam(sys_get_temp_dir(), 'wedding-guest-zip-');
        $archive = new ZipArchive;
        abort_unless($temporary && $archive->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'ZIP konnte nicht erstellt werden.');

        foreach ($media as $index => $item) {
            if (Storage::disk('local')->exists($item->original_path)) {
                $archive->addFile(
                    Storage::disk('local')->path($item->original_path),
                    sprintf('%04d-%s', $index + 1, basename($item->original_name))
                );
            }
        }

        $archive->close();

        return response()->download($temporary, $filename)->deleteFileAfterSend(true);
    }
}
