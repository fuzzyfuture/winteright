<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function ($table) {
            $table->integer('enabled_modes')->default(15);
        });
    }

    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('enabled_modes');
        });
    }
};
