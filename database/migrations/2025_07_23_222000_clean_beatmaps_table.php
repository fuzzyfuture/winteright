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
           $table->dropColumn('rating', 'chart_rank', 'chart_year_rank', 'rating_count', 'controversy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beatmaps', function (Blueprint $table) {
            $table->string('rating', 45)->nullable();
            $table->integer('chart_rank')->nullable();
            $table->integer('chart_year_rank')->nullable();
            $table->integer('rating_count')->nullable();
            $table->decimal('controversy', 10, 8)->nullable();
        });
    }
};
