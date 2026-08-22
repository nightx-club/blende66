<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = ['wedding_id', 'upload_session_id', 'type', 'original_name', 'internal_name', 'original_path', 'gallery_path', 'thumbnail_path', 'mime_type', 'file_size', 'video_duration', 'guest_name', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'file_size' => 'integer', 'video_duration' => 'integer'];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function uploadSession(): BelongsTo
    {
        return $this->belongsTo(UploadSession::class);
    }
}
