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
        Schema::dropIfExists('rating_labels');

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('osu_id')->unsigned()->nullable(false)->change();
            $table->bigInteger('id')->unsigned()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary(['id']);
            $table->dropColumn(['id', 'avatar']);
            $table->primary('osu_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('osu_id')->unsigned()->first()->change();
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->foreign('user_id')->references('osu_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary(['osu_id']);
            $table->bigInteger('osu_id')->unsigned()->nullable()->unique()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->id()->first();
            $table->string('avatar')->nullable()->after('osu_id');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
