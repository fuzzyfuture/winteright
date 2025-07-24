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
        Schema::create('beatmap_sets', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary()->unique();
            $table->bigInteger('creator_id')->unsigned();
            $table->timestamp('date_ranked')->nullable();
            $table->integer('genre')->nullable();
            $table->integer('lang')->nullable();
            $table->string('artist')->nullable();
            $table->string('title')->nullable();
            $table->boolean('has_storyboard')->default(false);
            $table->boolean('has_video')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beatmap_sets');
    }
};
