<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->string('couple_names');
            $table->string('slug')->unique();
            $table->date('wedding_date');
            $table->string('pin_hash');
            $table->string('cover_image_path')->nullable();
            $table->text('welcome_text')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('photo_max_mb')->default(25);
            $table->unsignedTinyInteger('photo_batch_max')->default(20);
            $table->unsignedSmallInteger('video_max_mb')->default(100);
            $table->unsignedSmallInteger('video_max_seconds')->default(180);
            $table->unsignedTinyInteger('video_batch_max')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weddings');
    }
};
