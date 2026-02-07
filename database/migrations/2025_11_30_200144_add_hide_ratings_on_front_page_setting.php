<?php

use App\Enums\HideRatingsOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('hide_ratings_temp')->nullable();
        });

        DB::table('users')
            ->where('hide_ratings', true)
            ->update(['hide_ratings_temp' => HideRatingsOption::ALL->value]);

        DB::table('users')
            ->where('hide_ratings', false)
            ->update(['hide_ratings_temp' => HideRatingsOption::NONE->value]);

        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('hide_ratings_temp')
                ->nullable(false)
                ->default(HideRatingsOption::NONE->value)
                ->change();

            $table->dropColumn('hide_ratings');
            $table->renameColumn('hide_ratings_temp', 'hide_ratings');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('hide_ratings_temp')->nullable();
        });

        DB::table('users')
            ->where('hide_ratings', HideRatingsOption::ALL->value)
            ->update(['hide_ratings_temp' => true]);

        DB::table('users')
            ->where('hide_ratings', '!=', HideRatingsOption::ALL->value)
            ->update(['hide_ratings_temp' => false]);

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('hide_ratings_temp')
                ->nullable(false)
                ->default(false)
                ->change();

            $table->dropColumn('hide_ratings');
            $table->renameColumn('hide_ratings_temp', 'hide_ratings');
        });
    }
};
