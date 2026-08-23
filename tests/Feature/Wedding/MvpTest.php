<?php

namespace Tests\Feature\Wedding;

use App\Models\Media;
use App\Models\UploadSession;
use App\Models\User;
use App\Models\Wedding;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class MvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_start_page_redirects_server_side_to_blende6_without_upload_anchor(): void
    {
        $response = $this->get('/')->assertRedirect('/h/blende6');

        $this->assertStringNotContainsString('#upload', $response->headers->get('Location'));
    }

    public function test_guests_need_the_correct_pin_and_access_is_saved_in_session(): void
    {
        $wedding = $this->wedding();
        $this->get(route('weddings.show', $wedding))->assertOk()->assertSee('Galerie-PIN');
        $this->post(route('weddings.unlock', $wedding), ['pin' => '0000'])->assertSessionHasErrors('pin');
        $this->post(route('weddings.unlock', $wedding), ['pin' => '123456'])->assertRedirect(route('weddings.show', $wedding))->assertSessionHas("wedding_access.{$wedding->id}", true);
        $this->get(route('weddings.show', $wedding))->assertOk()->assertSee('Fotos & Videos hochladen', false);
    }

    public function test_pin_attempts_are_rate_limited(): void
    {
        $wedding = $this->wedding(['slug' => 'rate-limit']);
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('weddings.unlock', $wedding), ['pin' => '0000']);
        }
        $this->post(route('weddings.unlock', $wedding), ['pin' => '0000'])->assertTooManyRequests();
    }

    public function test_media_cannot_be_accessed_through_another_wedding(): void
    {
        Storage::fake('local');
        $first = $this->wedding(['slug' => 'first']);
        $second = $this->wedding(['slug' => 'second']);
        Storage::disk('local')->put('weddings/1/gallery/file.webp', 'image');
        $media = Media::create(['wedding_id' => $first->id, 'type' => 'photo', 'original_name' => 'photo.jpg', 'internal_name' => Str::uuid(), 'original_path' => 'original.jpg', 'gallery_path' => 'weddings/1/gallery/file.webp', 'mime_type' => 'image/jpeg', 'file_size' => 5, 'is_published' => true]);
        $this->withSession(["wedding_access.{$second->id}" => true])->get(route('weddings.media.view', [$second, $media]))->assertNotFound();
    }

    public function test_valid_image_upload_is_optimized_and_published(): void
    {
        Storage::fake('local');
        $wedding = $this->wedding();
        $response = $this->withSession(["wedding_access.{$wedding->id}" => true])->postJson(route('weddings.upload', $wedding), [
            'batch_id' => (string) Str::uuid(),
            'guest_name' => 'Anna',
            'guest_email' => 'anna@example.com',
            'file' => UploadedFile::fake()->image('moment.jpg', 1200, 900)->size(1500),
        ]);
        $response->assertCreated()->assertJsonPath('media.type', 'photo');
        $media = Media::firstOrFail();
        $this->assertTrue($media->is_published);
        $this->assertSame('anna@example.com', $media->uploadSession->guest_email);
        Storage::disk('local')->assertExists($media->original_path);
        Storage::disk('local')->assertExists($media->gallery_path);
        Storage::disk('local')->assertExists($media->thumbnail_path);
        $this->withSession(["wedding_access.{$wedding->id}" => true])
            ->get(route('weddings.show', $wedding))
            ->assertOk()
            ->assertSeeText('Anna');
    }

    public function test_oversized_video_is_rejected_server_side(): void
    {
        Storage::fake('local');
        $wedding = $this->wedding(['video_max_mb' => 500]);
        $response = $this->withSession(["wedding_access.{$wedding->id}" => true])->postJson(route('weddings.upload', $wedding), [
            'batch_id' => (string) Str::uuid(),
            'guest_name' => 'Anna',
            'guest_email' => 'anna@example.com',
            'file' => UploadedFile::fake()->create('clip.mp4', 101 * 1024, 'video/mp4'),
        ]);
        $response->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_guest_name_is_required_for_uploads(): void
    {
        Storage::fake('local');
        $wedding = $this->wedding();

        $this->withSession(["wedding_access.{$wedding->id}" => true])->postJson(route('weddings.upload', $wedding), [
            'batch_id' => (string) Str::uuid(),
            'file' => UploadedFile::fake()->image('moment.jpg'),
        ])->assertUnprocessable()->assertJsonValidationErrors('guest_name');
    }

    public function test_guest_email_is_optional_for_uploads(): void
    {
        Storage::fake('local');
        $wedding = $this->wedding();

        $this->withSession(["wedding_access.{$wedding->id}" => true])->postJson(route('weddings.upload', $wedding), [
            'batch_id' => (string) Str::uuid(),
            'guest_name' => 'Anna',
            'file' => UploadedFile::fake()->image('moment.jpg'),
        ])->assertCreated();

        $this->assertNull(UploadSession::firstOrFail()->guest_email);
    }

    public function test_master_admin_can_delete_every_file_from_one_guest_upload(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $wedding = $this->wedding();
        $upload = UploadSession::create([
            'id' => (string) Str::uuid(),
            'wedding_id' => $wedding->id,
            'guest_name' => 'Anna',
            'guest_email' => 'anna@example.com',
            'expires_at' => now()->addHour(),
        ]);

        foreach ([1, 2] as $number) {
            $path = "weddings/{$wedding->id}/originals/{$number}.jpg";
            Storage::disk('local')->put($path, 'image');
            Media::create([
                'wedding_id' => $wedding->id,
                'upload_session_id' => $upload->id,
                'type' => 'photo',
                'original_name' => "moment-{$number}.jpg",
                'internal_name' => Str::uuid(),
                'original_path' => $path,
                'mime_type' => 'image/jpeg',
                'file_size' => 5,
                'guest_name' => 'Anna',
                'is_published' => true,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.weddings.media.index', $wedding))
            ->assertOk()
            ->assertSee('anna@example.com')
            ->assertSee('Komplettes Album löschen');

        $this->actingAs($admin)
            ->delete(route('admin.weddings.uploads.destroy', [$wedding, $upload]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('upload_sessions', ['id' => $upload->id]);
        $this->assertDatabaseCount('media', 0);
        Storage::disk('local')->assertMissing("weddings/{$wedding->id}/originals/1.jpg");
        Storage::disk('local')->assertMissing("weddings/{$wedding->id}/originals/2.jpg");
    }

    public function test_admin_routes_require_an_admin_account(): void
    {
        $this->get(route('admin.weddings.index'))->assertRedirect(route('admin.login'));
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get(route('admin.weddings.index'))->assertForbidden();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.weddings.index'))->assertOk()->assertSeeText('Hochzeiten & Events');
    }

    public function test_requested_master_admin_credentials_can_log_in(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post(route('admin.login.store'), [
            'email' => 'info@blende6.de',
            'password' => 'Chef73schwaab!',
        ])->assertRedirect(route('admin.weddings.index'));

        $this->assertAuthenticatedAs(User::query()->where('email', 'info@blende6.de')->firstOrFail());
        $this->assertDatabaseMissing('users', ['email' => 'info@blende-6.de']);
    }

    public function test_master_admin_can_delete_a_complete_guest_album_across_multiple_uploads(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $wedding = $this->wedding(['slug' => 'blende6']);
        $otherWedding = $this->wedding(['slug' => 'other-gallery']);
        $firstUpload = $this->uploadSession($wedding, 'Anna');
        $secondUpload = $this->uploadSession($wedding, ' anna ');
        $foreignUpload = $this->uploadSession($otherWedding, 'Anna');
        $first = $this->media($wedding, ['upload_session_id' => $firstUpload->id, 'guest_name' => 'Anna']);
        $second = $this->media($wedding, ['upload_session_id' => $secondUpload->id, 'guest_name' => ' anna ']);
        $foreign = $this->media($otherWedding, ['upload_session_id' => $foreignUpload->id, 'guest_name' => 'Anna']);
        foreach ([$first, $second, $foreign] as $item) {
            Storage::disk('local')->put($item->original_path, 'original');
            Storage::disk('local')->put($item->gallery_path, 'gallery');
            Storage::disk('local')->put($item->thumbnail_path, 'thumb');
        }

        $this->actingAs($admin)
            ->get(route('admin.weddings.media.index', $wedding))
            ->assertOk()
            ->assertSeeText('Album von Anna')
            ->assertSeeText('Komplettes Album löschen');

        $this->actingAs($admin)
            ->delete(route('admin.weddings.albums.destroy', $wedding), ['guest_name' => 'ANNA'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('media', ['id' => $first->id]);
        $this->assertDatabaseMissing('media', ['id' => $second->id]);
        $this->assertDatabaseHas('media', ['id' => $foreign->id, 'wedding_id' => $otherWedding->id]);
        $this->assertDatabaseMissing('upload_sessions', ['id' => $firstUpload->id]);
        $this->assertDatabaseMissing('upload_sessions', ['id' => $secondUpload->id]);
        $this->assertDatabaseHas('upload_sessions', ['id' => $foreignUpload->id]);
        Storage::disk('local')->assertMissing($first->original_path);
        Storage::disk('local')->assertMissing($second->original_path);
        Storage::disk('local')->assertExists($foreign->original_path);
    }

    public function test_master_admin_can_delete_one_media_item_without_touching_other_items(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $wedding = $this->wedding();
        $deleted = $this->media($wedding, ['original_name' => 'delete-me.jpg']);
        $kept = $this->media($wedding, ['original_name' => 'keep-me.jpg']);
        foreach ([$deleted, $kept] as $item) {
            Storage::disk('local')->put($item->original_path, 'original');
            Storage::disk('local')->put($item->gallery_path, 'gallery');
            Storage::disk('local')->put($item->thumbnail_path, 'thumb');
        }

        $this->actingAs($admin)
            ->delete(route('admin.weddings.media.destroy', [$wedding, $deleted]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('media', ['id' => $deleted->id]);
        $this->assertDatabaseHas('media', ['id' => $kept->id]);
        Storage::disk('local')->assertMissing($deleted->original_path);
        Storage::disk('local')->assertExists($kept->original_path);
    }

    public function test_master_admin_can_delete_a_complete_event_gallery(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $wedding = $this->wedding(['slug' => 'delete-gallery', 'cover_image_path' => 'covers/delete.webp']);
        $otherWedding = $this->wedding(['slug' => 'keep-gallery']);
        $deleted = $this->media($wedding);
        $kept = $this->media($otherWedding);
        foreach ([$deleted, $kept] as $item) {
            Storage::disk('local')->put($item->original_path, 'original');
        }
        Storage::disk('local')->put($wedding->cover_image_path, 'cover');

        $this->actingAs($admin)
            ->delete(route('admin.weddings.destroy', $wedding))
            ->assertRedirect(route('admin.weddings.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('weddings', ['id' => $wedding->id]);
        $this->assertDatabaseHas('weddings', ['id' => $otherWedding->id]);
        Storage::disk('local')->assertMissing($deleted->original_path);
        Storage::disk('local')->assertMissing('covers/delete.webp');
        Storage::disk('local')->assertExists($kept->original_path);
    }

    public function test_admin_can_download_a_qr_code(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $wedding = $this->wedding();
        $this->actingAs($admin)->get(route('admin.weddings.qr.download', $wedding))->assertOk()->assertHeader('content-type', 'image/png');
    }

    public function test_each_wedding_or_event_has_an_individual_qr_code(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $first = $this->wedding(['slug' => 'first-event']);
        $second = $this->wedding(['slug' => 'second-event']);

        $this->assertNotSame($first->guest_token, $second->guest_token);

        $firstQr = $this->actingAs($admin)->get(route('admin.weddings.qr.download', $first))->assertOk();
        $secondQr = $this->actingAs($admin)->get(route('admin.weddings.qr.download', $second))->assertOk();

        $this->assertNotSame($firstQr->getContent(), $secondQr->getContent());
    }

    public function test_qr_link_unlocks_the_gallery_without_a_pin(): void
    {
        $wedding = $this->wedding();

        $this->get(route('weddings.qr-access', $wedding->guest_token))
            ->assertRedirect(route('weddings.show', $wedding))
            ->assertSessionHas("wedding_access.{$wedding->id}", true);

        $this->get(route('weddings.show', $wedding))
            ->assertOk()
            ->assertSee('Fotos & Videos hochladen', false);
    }

    public function test_unlocked_guests_can_see_and_download_the_event_qr_code(): void
    {
        $wedding = $this->wedding();

        $this->get(route('weddings.qr', $wedding))->assertRedirect(route('weddings.show', $wedding));
        $this->withSession(["wedding_access.{$wedding->id}" => true])
            ->get(route('weddings.qr.download', $wedding))
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertDownload(Str::slug($wedding->couple_names).'-qr-code.png');
    }

    public function test_existing_blende6_record_is_renamed_without_losing_media_or_creating_a_second_gallery(): void
    {
        Storage::fake('local');
        $wedding = $this->wedding(['slug' => 'lina-und-chris', 'couple_names' => 'Blende6']);
        $media = $this->media($wedding, ['guest_name' => 'Bestehender Upload']);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('weddings', ['id' => $wedding->id, 'slug' => 'blende6', 'couple_names' => 'Blende6']);
        $this->assertDatabaseHas('media', ['id' => $media->id, 'wedding_id' => $wedding->id]);
        $this->assertDatabaseCount('weddings', 1);
        $this->get('/h/lina-und-chris')->assertNotFound();
        $this->get('/h/blende6')->assertOk()->assertSeeText('Blende6 öffnen');
    }

    public function test_gallery_shows_newest_published_media_first_and_never_media_from_another_gallery(): void
    {
        $wedding = $this->wedding(['slug' => 'blende6', 'couple_names' => 'Blende6']);
        $otherWedding = $this->wedding(['slug' => 'andere-galerie']);
        $this->media($wedding, ['guest_name' => 'Älterer Upload', 'created_at' => now()->subHour()]);
        $this->media($wedding, ['guest_name' => 'Neuester Upload', 'created_at' => now()]);
        $this->media($wedding, ['guest_name' => 'Versteckter Upload', 'is_published' => false]);
        $this->media($otherWedding, ['guest_name' => 'Fremde Galerie']);

        $this->withSession(["wedding_access.{$wedding->id}" => true])
            ->get(route('weddings.show', $wedding))
            ->assertOk()
            ->assertSeeTextInOrder(['Neuester Upload', 'Älterer Upload'])
            ->assertDontSeeText('Versteckter Upload')
            ->assertDontSeeText('Fremde Galerie');
    }

    public function test_gallery_filters_photos_and_videos_and_renders_videos_as_players(): void
    {
        $wedding = $this->wedding(['slug' => 'blende6', 'couple_names' => 'Blende6']);
        $this->media($wedding, ['type' => 'photo', 'guest_name' => 'Foto Upload']);
        $this->media($wedding, ['type' => 'video', 'guest_name' => 'Video Upload', 'mime_type' => 'video/mp4', 'thumbnail_path' => null, 'gallery_path' => null]);
        $session = ["wedding_access.{$wedding->id}" => true];

        $this->withSession($session)->get(route('weddings.show', $wedding))
            ->assertOk()
            ->assertSeeText('Alle')
            ->assertSeeText('Fotos')
            ->assertSeeText('Videos')
            ->assertSee('<video', false)
            ->assertSeeText('Foto Upload')
            ->assertSeeText('Video Upload');

        $this->withSession($session)->get(route('weddings.show', ['wedding' => $wedding, 'type' => 'photo']))
            ->assertOk()
            ->assertSee('data-media-guest="Foto Upload"', false)
            ->assertDontSee('data-media-guest="Video Upload"', false);

        $this->withSession($session)->get(route('weddings.show', ['wedding' => $wedding, 'type' => 'video']))
            ->assertOk()
            ->assertSee('data-media-guest="Video Upload"', false)
            ->assertDontSee('data-media-guest="Foto Upload"', false);
    }

    public function test_gallery_paginates_after_twenty_four_files(): void
    {
        $wedding = $this->wedding(['slug' => 'blende6', 'couple_names' => 'Blende6']);
        foreach (range(1, 25) as $number) {
            $this->media($wedding, ['guest_name' => "Upload {$number}"]);
        }

        $this->withSession(["wedding_access.{$wedding->id}" => true])
            ->get(route('weddings.show', $wedding))
            ->assertOk()
            ->assertSee('page=2', false);
    }

    public function test_all_media_and_each_guest_gallery_can_be_downloaded_as_isolated_zip_files(): void
    {
        Storage::fake('local');
        $wedding = $this->wedding(['slug' => 'blende6', 'couple_names' => 'Blende6']);
        $otherWedding = $this->wedding(['slug' => 'fremde-galerie']);
        $chris = $this->media($wedding, ['guest_name' => 'Chris', 'original_name' => 'chris.jpg']);
        $anna = $this->media($wedding, ['guest_name' => 'Anna', 'original_name' => 'anna.jpg']);
        $foreign = $this->media($otherWedding, ['guest_name' => 'Chris', 'original_name' => 'fremd.jpg']);
        foreach ([$chris, $anna, $foreign] as $item) {
            Storage::disk('local')->put($item->original_path, $item->original_name);
        }
        $session = ["wedding_access.{$wedding->id}" => true];

        $this->withSession($session)->get(route('weddings.show', $wedding))
            ->assertOk()
            ->assertSeeText('Alle als ZIP herunterladen')
            ->assertSeeText('Chris als ZIP')
            ->assertSeeText('Anna als ZIP');

        $guestResponse = $this->withSession($session)
            ->get(route('weddings.guest-album.download', ['wedding' => $wedding, 'guest' => 'Chris']))
            ->assertOk()
            ->assertDownload('blende6-chris-galerie.zip');
        $this->assertZipContainsExactly($guestResponse->baseResponse->getFile()->getPathname(), ['0001-chris.jpg']);

        $allResponse = $this->withSession($session)
            ->get(route('weddings.archive.download', $wedding))
            ->assertOk()
            ->assertDownload('blende6-alle-galerien.zip');
        $this->assertZipContainsExactly($allResponse->baseResponse->getFile()->getPathname(), ['0001-chris.jpg', '0002-anna.jpg']);
    }

    private function wedding(array $attributes = []): Wedding
    {
        return Wedding::create(array_merge([
            'couple_names' => 'Anna & Tom',
            'slug' => 'anna-und-tom-'.Str::lower(Str::random(5)),
            'wedding_date' => '2026-08-22',
            'pin_hash' => Hash::make('123456'),
            'welcome_text' => 'Willkommen',
            'is_active' => true,
            'photo_max_mb' => 25,
            'photo_batch_max' => 20,
            'video_max_mb' => 100,
            'video_max_seconds' => 180,
            'video_batch_max' => 5,
        ], $attributes));
    }

    private function media(Wedding $wedding, array $attributes = []): Media
    {
        $uuid = (string) Str::uuid();

        return Media::create(array_merge([
            'wedding_id' => $wedding->id,
            'type' => 'photo',
            'original_name' => $uuid.'.jpg',
            'internal_name' => $uuid,
            'original_path' => "weddings/{$wedding->id}/originals/{$uuid}.jpg",
            'gallery_path' => "weddings/{$wedding->id}/gallery/{$uuid}.webp",
            'thumbnail_path' => "weddings/{$wedding->id}/thumbs/{$uuid}.webp",
            'mime_type' => 'image/jpeg',
            'file_size' => 5,
            'guest_name' => 'Gast',
            'is_published' => true,
        ], $attributes));
    }

    private function uploadSession(Wedding $wedding, string $guestName): UploadSession
    {
        return UploadSession::create([
            'id' => (string) Str::uuid(),
            'wedding_id' => $wedding->id,
            'guest_name' => $guestName,
            'guest_email' => mb_strtolower(trim($guestName)).'@example.com',
            'expires_at' => now()->addHour(),
        ]);
    }

    private function assertZipContainsExactly(string $path, array $expected): void
    {
        $archive = new ZipArchive;
        $this->assertTrue($archive->open($path) === true);
        $names = [];
        for ($index = 0; $index < $archive->numFiles; $index++) {
            $names[] = $archive->getNameIndex($index);
        }
        $archive->close();
        sort($names);
        sort($expected);
        $this->assertSame($expected, $names);
    }
}
