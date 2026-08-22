<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('weddings')
            ->where('slug', 'lina-und-chris')
            ->update([
                'slug' => 'blende6',
                'couple_names' => 'Blende6',
                'welcome_text' => 'Willkommen in der Blende6 Galerie. Entdeckt Fotos und Videos oder teilt eure eigenen Aufnahmen.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('weddings')
            ->where('slug', 'blende6')
            ->update(['slug' => 'lina-und-chris', 'updated_at' => now()]);
    }
};
