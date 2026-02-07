<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beatmap_creators', function (Blueprint $table) {
            $table->bigInteger('beatmap_id')->unsigned();
            $table->bigInteger('creator_id')->unsigned();

            $table->foreign('beatmap_id')->references('id')->on('beatmaps');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beatmap_creators');
    }
};
