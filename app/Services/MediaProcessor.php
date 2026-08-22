<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Imagick;
use Throwable;

class MediaProcessor
{
    private const PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'heic', 'heif', 'webp'];

    private const PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/heic', 'image/heif', 'image/webp'];

    private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'webm'];

    private const VIDEO_MIMES = ['video/mp4', 'video/quicktime', 'video/webm'];

    public function detectType(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();
        if (in_array($extension, self::PHOTO_EXTENSIONS, true) && in_array($mime, self::PHOTO_MIMES, true)) {
            return 'photo';
        }
        if (in_array($extension, self::VIDEO_EXTENSIONS, true) && in_array($mime, self::VIDEO_MIMES, true)) {
            return 'video';
        }
        throw ValidationException::withMessages(['file' => 'Dieser Dateityp ist nicht erlaubt oder die Datei wurde manipuliert.']);
    }

    public function store(Wedding $wedding, UploadedFile $file, ?string $guestName, ?string $uploadSessionId = null): Media
    {
        $type = $this->detectType($file);
        $maxMegabytes = $type === 'photo' ? $wedding->photo_max_mb : min($wedding->video_max_mb, 100);
        $maxBytes = $maxMegabytes * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages(['file' => 'Die Datei überschreitet das Limit von '.($maxBytes / 1024 / 1024).' MB.']);
        }

        $uuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension());
        $base = "weddings/{$wedding->id}";
        $originalPath = "{$base}/originals/{$uuid}.{$extension}";
        $galleryPath = null;
        $thumbnailPath = null;
        $duration = null;

        if ($type === 'photo') {
            [$galleryPath, $thumbnailPath] = $this->processPhoto($wedding, $file, $uuid);
        } else {
            $duration = $this->videoDuration($file);
            if ($duration > $wedding->video_max_seconds) {
                throw ValidationException::withMessages(['file' => "Das Video ist länger als {$wedding->video_max_seconds} Sekunden."]);
            }
        }

        Storage::disk('local')->putFileAs("{$base}/originals", $file, "{$uuid}.{$extension}");
        try {
            return $wedding->media()->create([
                'upload_session_id' => $uploadSessionId,
                'type' => $type,
                'original_name' => Str::limit(basename($file->getClientOriginalName()), 240, ''),
                'internal_name' => $uuid,
                'original_path' => $originalPath,
                'gallery_path' => $galleryPath,
                'thumbnail_path' => $thumbnailPath,
                'mime_type' => (string) $file->getMimeType(),
                'file_size' => $file->getSize(),
                'video_duration' => $duration,
                'guest_name' => $guestName ?: null,
                'is_published' => true,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_filter([$originalPath, $galleryPath, $thumbnailPath]));
            throw $exception;
        }
    }

    private function processPhoto(Wedding $wedding, UploadedFile $file, string $uuid): array
    {
        try {
            $image = new Imagick($file->getRealPath());
            if (method_exists($image, 'autoOrient')) {
                $image->autoOrient();
            }
            $image->setIteratorIndex(0);
            $image->stripImage();
            $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
            $image->thumbnailImage(2000, 2000, true, true);
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality(82);
            $galleryPath = "weddings/{$wedding->id}/gallery/{$uuid}.webp";
            Storage::disk('local')->put($galleryPath, $image->getImagesBlob());

            $thumb = clone $image;
            $thumb->cropThumbnailImage(720, 540);
            $thumb->stripImage();
            $thumb->setImageFormat('webp');
            $thumb->setImageCompressionQuality(76);
            $thumbnailPath = "weddings/{$wedding->id}/thumbs/{$uuid}.webp";
            Storage::disk('local')->put($thumbnailPath, $thumb->getImagesBlob());
            $thumb->clear();
            $image->clear();

            return [$galleryPath, $thumbnailPath];
        } catch (Throwable) {
            throw ValidationException::withMessages(['file' => 'Das Foto konnte nicht verarbeitet werden. Bitte exportiert es erneut als JPG oder PNG.']);
        }
    }

    private function videoDuration(UploadedFile $file): int
    {
        $analyzer = new \getID3;
        $info = $analyzer->analyze($file->getRealPath());
        $seconds = $info['playtime_seconds'] ?? null;
        if (! is_numeric($seconds)) {
            throw ValidationException::withMessages(['file' => 'Die Videolänge konnte nicht geprüft werden. Bitte verwendet MP4, MOV oder WebM.']);
        }

        return (int) ceil((float) $seconds);
    }
}
