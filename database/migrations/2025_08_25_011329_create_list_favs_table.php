<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_list_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('list_id')->constrained('user_lists')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'list_id']);

            $table->index(['user_id']);
            $table->index(['list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_list_favorites');
    }
};
