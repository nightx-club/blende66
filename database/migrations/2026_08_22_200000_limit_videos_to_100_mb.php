<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('weddings')->where('video_max_mb', '>', 100)->update(['video_max_mb' => 100]);
    }

    public function down(): void
    {
        // Bestehende individuelle Werte werden bewusst nicht wieder erhöht.
    }
};
