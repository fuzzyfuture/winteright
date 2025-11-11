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
        Schema::table('ratings', function (Blueprint $table) {
           $table->dropForeign('ratings_beatmap_id_foreign');
           $table->foreign('beatmap_id')->references('id')->on('beatmaps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign('ratings_beatmap_id_foreign');
            $table->foreign('beatmap_id')->references('id')->on('beatmaps');
        });
    }
};
