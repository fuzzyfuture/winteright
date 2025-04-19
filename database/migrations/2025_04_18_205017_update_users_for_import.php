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
            $table->boolean('banned')->default(false);
            $table->decimal('weight', 6, 4)->nullable();
            $table->boolean('hide_ratings')->default(false);
            $table->text('bio')->nullable();
            $table->string('title', 50)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('ip_address', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'banned',
                'weight',
                'hide_ratings',
                'bio',
                'title',
                'last_seen_at',
                'ip_address',
            ]);
        });
    }
};
