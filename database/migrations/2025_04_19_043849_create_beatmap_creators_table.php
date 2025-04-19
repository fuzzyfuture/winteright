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
        Schema::create('beatmap_creators', function (Blueprint $table) {
            $table->unsignedInteger('beatmap_id');
            $table->unsignedBigInteger('creator_id');
            $table->primary(['beatmap_id', 'creator_id']);

            $table->foreign('beatmap_id')->references('beatmap_id')->on('beatmaps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beatmap_creators');
    }
};
