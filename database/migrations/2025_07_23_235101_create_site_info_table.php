<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_info', function (Blueprint $table) {
            $table->timestamp('last_synced_ranked_beatmaps')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_info');
    }
};
