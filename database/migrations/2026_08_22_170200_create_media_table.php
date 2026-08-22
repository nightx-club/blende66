<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['photo', 'video'])->index();
            $table->string('original_name');
            $table->uuid('internal_name')->unique();
            $table->string('original_path');
            $table->string('gallery_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->unsignedSmallInteger('video_duration')->nullable();
            $table->string('guest_name', 80)->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
            $table->index(['wedding_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
