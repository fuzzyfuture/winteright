<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_info', function (Blueprint $table) {
            $table->timestamp('last_updated_charts')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_info', function (Blueprint $table) {
            $table->dropColumn('last_updated_charts');
        });
    }
};
