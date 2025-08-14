<?php

use App\Http\Controllers\Auth\OsuController;
use App\Http\Controllers\BeatmapSetController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserListController;
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
        Route::get('/users/{id}', 'show')->name('show');
        Route::get('/users/{id}/ratings', 'ratings')->name('ratings');
    });

Route::controller(BeatmapSetController::class)
    ->as('beatmaps.')
    ->group(function () {
        Route::get('/mapsets/{set}', 'show')->name('show');
        Route::get('/mapsets/{set}/ratings', 'ratings')->name('ratings');
    });

Route::get('/charts', [ChartsController::class, 'index'])->name('charts.index');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');

Route::middleware('auth')->group(function () {
    Route::post('/beatmaps/{id}/rate', [RatingController::class, 'update'])->name('ratings.update');

    Route::get('/lists/new', [UserListController::class, 'getNew'])->name('lists.new');
    Route::post('/lists/new', [UserListController::class, 'postNew'])->name('lists.new.post');
});

Route::controller(UserListController::class)
    ->as('lists.')
    ->group(function () {
        Route::get('/lists', 'index')->name('index');
        Route::get('/lists/{id}', 'show')->name('show');
    });

