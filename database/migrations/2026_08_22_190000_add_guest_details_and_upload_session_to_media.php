<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upload_sessions', function (Blueprint $table) {
            $table->string('guest_name', 80)->nullable()->after('wedding_id');
            $table->string('guest_email', 254)->nullable()->after('guest_name');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreignUuid('upload_session_id')
                ->nullable()
                ->after('wedding_id')
                ->constrained('upload_sessions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropConstrainedForeignId('upload_session_id');
        });

        Schema::table('upload_sessions', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_email']);
        });
    }
};
