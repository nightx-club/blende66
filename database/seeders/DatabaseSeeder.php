<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\UploadSession;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->whereIn('email', ['info@blende6.de', 'info@blende-6.de'])->first() ?? new User;
        $admin->fill([
            'name' => 'Blende6 Master Admin',
            'email' => 'info@blende6.de',
            'password' => 'Chef73schwaab!',
            'is_admin' => true,
        ]);
        $admin->email_verified_at = now();
        $admin->save();
        User::query()->where('email', 'info@blende-6.de')->whereKeyNot($admin->id)->delete();

        $wedding = Wedding::query()->where('slug', 'blende6')->first()
            ?? Wedding::query()->where('couple_names', 'Blende6')->first()
            ?? Wedding::query()->where('slug', 'lina-und-chris')->first()
            ?? new Wedding;

        $wedding->fill([
            'slug' => 'blende6',
            'couple_names' => 'Blende6',
            'wedding_date' => '2026-08-22',
            'pin_hash' => Hash::make('220826'),
            'welcome_text' => 'Willkommen in der Blende6 Galerie. Entdeckt Fotos und Videos oder teilt eure eigenen Aufnahmen.',
            'is_active' => true,
            'photo_max_mb' => 25,
            'photo_batch_max' => 20,
            'video_max_mb' => 100,
            'video_max_seconds' => 180,
            'video_batch_max' => 5,
        ])->save();

        $source = public_path('images/blende6/hero.jpg');
        $coverPath = 'covers/blende6.webp';
        if ($wedding->cover_image_path && $wedding->cover_image_path !== $coverPath && Storage::disk('local')->exists($wedding->cover_image_path)) {
            Storage::disk('local')->move($wedding->cover_image_path, $coverPath);
            $wedding->update(['cover_image_path' => $coverPath]);
        }
        if (! $wedding->cover_image_path && is_file($source)) {
            $image = new Imagick($source);
            if (method_exists($image, 'autoOrient')) {
                $image->autoOrient();
            }
            $image->stripImage();
            $image->thumbnailImage(2000, 1400, true, true);
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality(84);
            Storage::disk('local')->put($coverPath, $image->getImagesBlob());
            $image->clear();
            $wedding->update(['cover_image_path' => $coverPath]);
        }

        $this->seedChrisAlbum($wedding);
    }

    private function seedChrisAlbum(Wedding $wedding): void
    {
        if ($wedding->media()->where('guest_name', 'Chris')->exists()) {
            return;
        }

        $upload = UploadSession::updateOrCreate(
            ['id' => '00000000-0000-4000-8000-000000000001'],
            [
                'wedding_id' => $wedding->id,
                'guest_name' => 'Chris',
                'guest_email' => 'info@chris.example',
                'photo_count' => 6,
                'video_count' => 0,
                'expires_at' => now()->addYears(10),
            ]
        );

        foreach ([1, 3, 8, 12, 16, 20] as $position => $number) {
            $source = public_path(sprintf('images/blende6/weddings/wedding-%03d.jpg', $number));
            if (! is_file($source)) {
                continue;
            }

            $key = sprintf('chris-%02d', $position + 1);
            $originalPath = "weddings/{$wedding->id}/originals/{$key}.jpg";
            $galleryPath = "weddings/{$wedding->id}/gallery/{$key}.webp";
            $thumbnailPath = "weddings/{$wedding->id}/thumbs/{$key}.webp";
            Storage::disk('local')->put($originalPath, file_get_contents($source));

            $image = new Imagick($source);
            if (method_exists($image, 'autoOrient')) {
                $image->autoOrient();
            }
            $image->stripImage();

            $gallery = clone $image;
            $gallery->thumbnailImage(1800, 1800, true, true);
            $gallery->setImageFormat('webp');
            $gallery->setImageCompressionQuality(84);
            Storage::disk('local')->put($galleryPath, $gallery->getImagesBlob());
            $gallery->clear();

            $thumbnail = clone $image;
            $thumbnail->cropThumbnailImage(720, 720);
            $thumbnail->setImageFormat('webp');
            $thumbnail->setImageCompressionQuality(78);
            Storage::disk('local')->put($thumbnailPath, $thumbnail->getImagesBlob());
            $thumbnail->clear();
            $image->clear();

            Media::firstOrCreate(
                ['wedding_id' => $wedding->id, 'original_path' => $originalPath],
                [
                    'upload_session_id' => $upload->id,
                    'type' => 'photo',
                    'original_name' => sprintf('hochzeitsmoment-%02d.jpg', $position + 1),
                    'internal_name' => (string) Str::uuid(),
                    'gallery_path' => $galleryPath,
                    'thumbnail_path' => $thumbnailPath,
                    'mime_type' => 'image/jpeg',
                    'file_size' => filesize($source),
                    'guest_name' => 'Chris',
                    'is_published' => true,
                ]
            );
        }
    }
}
