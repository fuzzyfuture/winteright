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
        Schema::table('beatmaps', function (Blueprint $table) {
            $table->float('weighted_avg')->unsigned()->nullable()->default(0)->change();
            $table->float('bayesian_avg')->unsigned()->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beatmaps', function (Blueprint $table) {
            $table->float('weighted_avg')->unsigned()->default(0)->change();
            $table->float('bayesian_avg')->unsigned()->default(0)->change();
        });
    }
};
