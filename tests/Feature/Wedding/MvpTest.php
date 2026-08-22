<?php

namespace Tests\Feature\Wedding;

use App\Models\Media;
use App\Models\UploadSession;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_need_the_correct_pin_and_access_is_saved_in_session(): void
    {
        $wedding = $this->wedding();
        $this->get(route('weddings.show', $wedding))->assertOk()->assertSee('Hochzeits-PIN');
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
            ->assertSee('Gesamten Upload löschen');

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

    public function test_guest_media_is_grouped_into_named_albums_with_downloads(): void
    {
        Storage::fake('local');
        $wedding = $this->wedding();
        $otherWedding = $this->wedding(['slug' => 'anderes-event']);

        foreach ([[$wedding, 'Chris', 'chris.jpg'], [$wedding, 'Anna', 'anna.jpg'], [$otherWedding, 'Chris', 'fremd.jpg']] as [$event, $guest, $filename]) {
            $path = "weddings/{$event->id}/originals/{$filename}";
            Storage::disk('local')->put($path, 'image');
            Media::create([
                'wedding_id' => $event->id,
                'type' => 'photo',
                'original_name' => $filename,
                'internal_name' => (string) Str::uuid(),
                'original_path' => $path,
                'gallery_path' => $path,
                'thumbnail_path' => $path,
                'mime_type' => 'image/jpeg',
                'file_size' => 5,
                'guest_name' => $guest,
                'is_published' => true,
            ]);
        }

        $this->withSession(["wedding_access.{$wedding->id}" => true])
            ->get(route('weddings.show', $wedding))
            ->assertOk()
            ->assertSeeText('Chris')
            ->assertSeeText('Anna')
            ->assertSee('data-src=', false);

        $this->withSession(["wedding_access.{$wedding->id}" => true])
            ->get(route('weddings.guest-album.download', ['wedding' => $wedding, 'guest' => 'Chris']))
            ->assertOk()
            ->assertDownload($wedding->slug.'-chris-album.zip');
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
}
