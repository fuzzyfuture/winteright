<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beatmaps', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary()->unique();
            $table->bigInteger('set_id')->unsigned();
            $table->string('difficulty_name')->nullable();
            $table->tinyInteger('mode')->unsigned()->default(0);
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->float('sr')->unsigned()->default(0);
            $table->float('weighted_avg')->unsigned()->default(0);
            $table->float('bayesian_avg')->unsigned()->default(0);
            $table->boolean('blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            $table->timestamps();

            $table->foreign('set_id')->references('id')->on('beatmap_sets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beatmaps');
    }
};
