<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeatmapSetController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserListController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::controller(AuthController::class)
    ->as('auth.')
    ->group(function () {
        Route::get('/login', 'loginRedirect')->name('login');
        Route::get('/auth/redirect', 'redirect')->name('redirect');
        Route::get('/oauth/osu/callback', 'callback')->name('callback');
        Route::post('/logout', 'logout')->name('logout');
    });

Route::controller(UserController::class)
    ->as('users.')
    ->group(function () {
        Route::middleware('auth')->group(function () {
            Route::post('/modes', 'postModes')->name('postModes');
        });

        Route::get('/users/{id}', 'show')->name('show');
        Route::get('/users/{id}/ratings', 'ratings')->name('ratings');
        Route::get('/users/{id}/lists', 'lists')->name('lists');
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
});

Route::controller(UserListController::class)
    ->as('lists.')
    ->group(function () {
        Route::middleware('auth')->group(function () {
            Route::get('/lists/new', 'getNew')->name('new');
            Route::post('/lists/new', 'postNew')->name('new.post');

            Route::get('/lists/{id}/edit', 'getEdit')->name('edit');
            Route::post('/lists/{id}/edit', 'postEdit')->name('edit.post');

            Route::get('/lists/{id}/edit-items', 'getEditItems')->name('edit-items');
            Route::post('/list-items/{id}/edit', 'postEditItem')->name('edit-item.post');
            Route::delete('/list-items/{id}/delete', 'deleteItem')->name('delete-item');

            Route::delete('/lists/{id}/delete', 'delete')->name('delete');

            Route::get('/lists/add', 'getAddItem')->name('add');
            Route::post('/lists/add', 'postAddItem')->name('add.post');

            Route::post('/lists/{id}/favorite', 'favorite')->name('favorite');
            Route::post('/lists/{id}/unfavorite', 'unfavorite')->name('unfavorite');

            Route::get('/lists/favorites', 'favorites')->name('favorites');
        });

        Route::get('/lists', 'index')->name('index');
        Route::get('/lists/{id}', 'show')->name('show');
    });

