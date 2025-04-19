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
        Schema::create('beatmaps', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('beatmap_id')->unique();
            $table->unsignedInteger('set_id');
            $table->string('difficulty_name')->nullable();
            $table->tinyInteger('mode')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->float('sr')->default(0);
            $table->string('rating', 45)->nullable();
            $table->integer('chart_rank')->nullable();
            $table->integer('chart_year_rank')->nullable();
            $table->integer('rating_count')->nullable();
            $table->float('weighted_avg')->nullable();
            $table->boolean('blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            $table->decimal('controversy', 10, 8)->nullable();
            $table->timestamps();

            $table->foreign('set_id')->references('set_id')->on('beatmap_sets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beatmaps');
    }
};
