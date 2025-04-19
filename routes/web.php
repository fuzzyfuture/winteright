<?php

use App\Http\Controllers\Auth\OsuController;
use App\Http\Controllers\BeatmapSetController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::controller(OsuController::class)
    ->as('auth.osu.')
    ->group(function () {
        Route::get('/login', 'loginRedirect')->name('login');
        Route::get('/auth/redirect', 'redirect')->name('redirect');
        Route::get('/oauth/osu/callback', 'callback')->name('callback');
        Route::post('/logout', 'logout')->name('logout');
    });

Route::controller(UserController::class)
    ->as('users.')
    ->group(function () {
        Route::get('/users/{osuId}', [UserController::class, 'showByOsuId'])->name('show');
    });

Route::get('/charts', [ChartsController::class, 'index'])->name('charts.index');
Route::get('/mapsets/{set}', [BeatmapSetController::class, 'show'])->name('beatmaps.show');

Route::middleware('auth')->post('/beatmaps/{beatmap}/rate', [RatingController::class, 'update'])->name('ratings.update');
