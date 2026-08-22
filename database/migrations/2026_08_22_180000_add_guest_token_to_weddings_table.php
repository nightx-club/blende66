<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->string('guest_token', 64)->nullable()->unique()->after('pin_hash');
        });

        DB::table('weddings')->whereNull('guest_token')->orderBy('id')->eachById(function ($wedding) {
            DB::table('weddings')->where('id', $wedding->id)->update(['guest_token' => Str::random(48)]);
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropUnique(['guest_token']);
            $table->dropColumn('guest_token');
        });
    }
};
