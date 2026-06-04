<?php

use App\Http\Controllers\Api\Mobile\Admin\MbAdminAlbumController;
use App\Http\Controllers\Api\Mobile\Admin\MbAdminAnalyticsController;
use App\Http\Controllers\Api\Mobile\Admin\MbAdminArtistController;
use App\Http\Controllers\Api\Mobile\Admin\MbAdminGenreController;
use App\Http\Controllers\Api\Mobile\Admin\MbAdminSongController;
use App\Http\Controllers\Api\Mobile\Admin\MbAdminTagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Admin API (Android app only)
|--------------------------------------------------------------------------
| Prefix: /api/mb/admin/*
| Auth:   Sanctum Bearer token + role:admin
| Controllers live in: app/Http/Controllers/Api/Mobile/Admin/
*/

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('mb/admin')
    ->name('api.mb.admin.')
    ->group(function () {
        Route::get('analytics', [MbAdminAnalyticsController::class, 'index'])
            ->name('analytics.index');

        Route::get('tags', [MbAdminTagController::class, 'index'])->name('tags.index');
        Route::post('tags', [MbAdminTagController::class, 'store'])->name('tags.store');
        Route::put('tags/{tag}', [MbAdminTagController::class, 'update'])->name('tags.update');
        Route::patch('tags/{tag}/status', [MbAdminTagController::class, 'updateStatus'])->name('tags.status');
        Route::patch('tags/{tag}/show-as-row', [MbAdminTagController::class, 'updateShowAsRow'])->name('tags.show-as-row');
        Route::delete('tags/{tag}', [MbAdminTagController::class, 'destroy'])->name('tags.destroy');

        Route::get('genres', [MbAdminGenreController::class, 'index'])->name('genres.index');
        Route::post('genres', [MbAdminGenreController::class, 'store'])->name('genres.store');
        Route::put('genres/{genre}', [MbAdminGenreController::class, 'update'])->name('genres.update');
        Route::patch('genres/{genre}/status', [MbAdminGenreController::class, 'updateStatus'])->name('genres.status');
        Route::patch('genres/{genre}/show-as-row', [MbAdminGenreController::class, 'updateShowAsRow'])->name('genres.show-as-row');
        Route::delete('genres/{genre}', [MbAdminGenreController::class, 'destroy'])->name('genres.destroy');

        Route::get('albums', [MbAdminAlbumController::class, 'index'])->name('albums.index');
        Route::put('albums/{album}', [MbAdminAlbumController::class, 'update'])->name('albums.update');
        Route::post('albums/{album}/approve', [MbAdminAlbumController::class, 'approve'])->name('albums.approve');
        Route::post('albums/{album}/hide', [MbAdminAlbumController::class, 'hide'])->name('albums.hide');
        Route::post('albums/{album}/report', [MbAdminAlbumController::class, 'report'])->name('albums.report');

        Route::post('songs/{song}/approve', [MbAdminSongController::class, 'approve'])->name('songs.approve');
        Route::post('songs/{song}/hide', [MbAdminSongController::class, 'hide'])->name('songs.hide');
        Route::post('songs/{song}/report', [MbAdminSongController::class, 'report'])->name('songs.report');

        Route::post('artists', [MbAdminArtistController::class, 'store'])->name('artists.store');
    });
