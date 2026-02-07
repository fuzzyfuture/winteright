<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('beatmap_creators', function (Blueprint $table) {
            $table->unique(['beatmap_id', 'creator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beatmap_creators', function (Blueprint $table) {
            $table->dropUnique(['beatmap_id', 'creator_id']);
        });
    }
};
