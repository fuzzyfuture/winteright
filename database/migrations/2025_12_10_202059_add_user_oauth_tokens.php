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
        Schema::table('users', function (Blueprint $table) {
            $table->text('osu_access_token')->nullable();
            $table->text('osu_refresh_token')->nullable();
            $table->timestamp('osu_token_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('osu_access_token');
            $table->dropColumn('osu_refresh_token');
            $table->dropColumn('osu_token_expires_at');
        });
    }
};
