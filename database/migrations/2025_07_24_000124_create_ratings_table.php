<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('beatmap_id')->unsigned();
            $table->tinyInteger('score')->unsigned();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('beatmap_id')->references('id')->on('beatmaps');
            $table->unique(['user_id', 'beatmap_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
