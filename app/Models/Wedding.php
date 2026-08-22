<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wedding extends Model
{
    use HasFactory;

    protected $fillable = ['couple_names', 'slug', 'wedding_date', 'pin_hash', 'cover_image_path', 'welcome_text', 'is_active', 'photo_max_mb', 'photo_batch_max', 'video_max_mb', 'video_max_seconds', 'video_batch_max'];

    protected function casts(): array
    {
        return ['wedding_date' => 'date', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Wedding $wedding) {
            $wedding->guest_token ??= Str::random(48);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function uploadSessions(): HasMany
    {
        return $this->hasMany(UploadSession::class);
    }
}
