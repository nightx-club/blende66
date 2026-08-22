<?php

namespace App\Http\Controllers;

use App\Models\UploadSession;
use App\Models\Wedding;
use App\Services\MediaProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WeddingUploadController extends Controller
{
    public function store(Request $request, Wedding $wedding, MediaProcessor $processor): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file'],
            'batch_id' => ['required', 'uuid'],
            'guest_name' => ['required', 'string', 'max:80'],
            'guest_email' => ['nullable', 'email', 'max:254'],
        ]);
        $type = $processor->detectType($validated['file']);
        $session = $this->reserveBatchSlot(
            $wedding,
            $validated['batch_id'],
            $type,
            $validated['guest_name'],
            $validated['guest_email'] ?? null,
        );
        $media = $processor->store($wedding, $validated['file'], $session->guest_name, $session->id);

        return response()->json(['message' => 'Upload erfolgreich.', 'media' => [
            'id' => $media->id,
            'type' => $media->type,
            'guest_name' => $media->guest_name,
            'view_url' => route('weddings.media.view', [$wedding, $media]),
            'download_url' => route('weddings.media.download', [$wedding, $media]),
        ]], 201);
    }

    private function reserveBatchSlot(Wedding $wedding, string $batchId, string $type, string $guestName, ?string $guestEmail): UploadSession
    {
        return DB::transaction(function () use ($wedding, $batchId, $type, $guestName, $guestEmail) {
            $normalizedEmail = $guestEmail ? mb_strtolower($guestEmail) : null;
            $session = UploadSession::query()->whereKey($batchId)->lockForUpdate()->first();
            if ($session && $session->wedding_id !== $wedding->id) {
                throw ValidationException::withMessages(['file' => 'Ungültiger Upload-Vorgang.']);
            }
            if (! $session) {
                $session = UploadSession::create([
                    'id' => $batchId,
                    'wedding_id' => $wedding->id,
                    'guest_name' => $guestName,
                    'guest_email' => $normalizedEmail,
                    'expires_at' => now()->addHour(),
                ]);
            } elseif ($session->expires_at->isPast()) {
                $session->update([
                    'guest_name' => $guestName,
                    'guest_email' => $normalizedEmail,
                    'photo_count' => 0,
                    'video_count' => 0,
                    'expires_at' => now()->addHour(),
                ]);
            } elseif ($session->guest_email !== $normalizedEmail) {
                throw ValidationException::withMessages(['guest_email' => 'Die E-Mail-Adresse wurde während des Uploads geändert. Bitte startet den Upload neu.']);
            }

            $column = $type === 'photo' ? 'photo_count' : 'video_count';
            $limit = $type === 'photo' ? $wedding->photo_batch_max : $wedding->video_batch_max;
            if ($session->{$column} >= $limit) {
                throw ValidationException::withMessages(['file' => "Pro Upload sind höchstens {$limit} ".($type === 'photo' ? 'Fotos' : 'Videos').' erlaubt.']);
            }
            $session->increment($column);

            return $session->refresh();
        });
    }
}
