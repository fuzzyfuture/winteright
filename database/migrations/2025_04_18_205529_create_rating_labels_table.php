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
        Schema::create('rating_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('rating_0_0')->default('');
            $table->string('rating_0_5')->default('');
            $table->string('rating_1_0')->default('');
            $table->string('rating_1_5')->default('');
            $table->string('rating_2_0')->default('');
            $table->string('rating_2_5')->default('');
            $table->string('rating_3_0')->default('');
            $table->string('rating_3_5')->default('');
            $table->string('rating_4_0')->default('');
            $table->string('rating_4_5')->default('');
            $table->string('rating_5_0')->default('');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_labels');
    }
};
