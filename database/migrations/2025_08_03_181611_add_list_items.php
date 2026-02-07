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
        Schema::create('user_list_items', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('list_id')->unsigned();
            $table->morphs('item');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('list_id')->references('id')->on('user_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_list_items');
    }
};
