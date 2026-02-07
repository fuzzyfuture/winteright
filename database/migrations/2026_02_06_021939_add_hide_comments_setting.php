<?php

use App\Enums\HideCommentsOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('hide_comments')
                ->after('hide_ratings')
                ->nullable(false)
                ->default(HideCommentsOption::NONE);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hide_comments');
        });
    }
};
