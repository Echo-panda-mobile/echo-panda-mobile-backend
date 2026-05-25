<?php

use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\Artist\AnalyticsController;
use App\Http\Controllers\Api\Artist\UploadController;
use App\Http\Controllers\Api\Streaming\AudioStreamController;
use App\Http\Controllers\Api\Streaming\LyricsController;
use App\Http\Controllers\Api\Streaming\PlaybackController;
use App\Http\Controllers\Api\Streaming\StreamTicketController;
use App\Http\Controllers\Api\ListenHistoryController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SongController;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/firebase/login', [AuthController::class, 'firebaseLogin'])->name('api.firebase.login');

// Public Routes (no authentication required)
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

// Public Album and Song Routes (readable by everyone)
Route::get('/albums', [AlbumController::class, 'index'])->name('api.albums.index');
Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('api.albums.show');
Route::get('/albums/{albumId}/songs', [SongController::class, 'getByAlbum'])->name('api.albums.songs');
Route::get('/albums/{album}/cover-url', [AlbumController::class, 'coverUrl'])->name('api.albums.cover-url');
Route::get('/songs', [SongController::class, 'index'])->name('api.songs.index');
Route::get('/songs/{song}', [SongController::class, 'show'])->name('api.songs.show');
Route::get('/stats/most-played-songs', [ListenHistoryController::class, 'mostPlayedSongs'])->name('api.stats.most-played-songs');
Route::get('/stats/most-played-albums', [ListenHistoryController::class, 'mostPlayedAlbums'])->name('api.stats.most-played-albums');
Route::get('/artists', [\App\Http\Controllers\Api\Artist\ArtistController::class, 'index'])->name('api.artists.index');
Route::get('/artists/{artist}/image-url', [\App\Http\Controllers\Api\Artist\ArtistController::class, 'imageUrl'])->name('api.artists.image-url');

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User Authentication Routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    
    // User Management (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users/by-role', [AuthController::class, 'usersByRole'])
            ->name('api.users.by-role');
    });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::get('/profile/favorite-songs', [ProfileController::class, 'getFavoriteSongs'])->name('api.profile.favorite-songs');
    Route::get('/profile/favorite-albums', [ProfileController::class, 'getFavoriteAlbums'])->name('api.profile.favorite-albums');

    // Artist/Publisher Routes
    Route::middleware('role:artist,publicer,admin')->group(function () {
        Route::post('/upload/media/presign', [UploadController::class, 'presignMedia'])
            ->name('api.upload.media.presign');

        Route::delete('/upload/media', [UploadController::class, 'deleteMedia'])
            ->name('api.upload.media.delete');

        // Album Routes (create/update/delete protected)
        Route::post('/albums', [AlbumController::class, 'store'])
            ->name('api.albums.store');
        Route::put('/albums/{album}', [AlbumController::class, 'update'])
            ->name('api.albums.update');
        Route::delete('/albums/{album}', [AlbumController::class, 'destroy'])
            ->name('api.albums.destroy');

        // Song Routes (create/update/delete protected)
        Route::post('/songs', [SongController::class, 'store'])
            ->name('api.songs.store');
        Route::put('/songs/{song}', [SongController::class, 'update'])
            ->name('api.songs.update');
        Route::delete('/songs/{song}', [SongController::class, 'destroy'])
            ->name('api.songs.destroy');

        Route::get('/artist/analytics', [AnalyticsController::class, 'show'])
            ->name('api.artist.analytics.show');
    });

    // Allow creating an artist profile for authenticated users who are not artists yet
    Route::post('/artist/create', [\App\Http\Controllers\Api\Artist\ArtistController::class, 'store'])
        ->middleware('auth:sanctum')
        ->name('api.artist.create');

    // Favorites Routes
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('api.favorites.index');
    Route::post('/favorites/songs', [FavoriteController::class, 'addSong'])->name('api.favorites.add-song');
    Route::post('/favorites/albums', [FavoriteController::class, 'addAlbum'])->name('api.favorites.add-album');
    Route::post('/favorites/songs/check', [FavoriteController::class, 'checkSong'])->name('api.favorites.check-song');
    Route::post('/favorites/albums/check', [FavoriteController::class, 'checkAlbum'])->name('api.favorites.check-album');
    Route::post('/favorites/songs/remove', [FavoriteController::class, 'removeSong'])->name('api.favorites.remove-song');
    Route::post('/favorites/albums/remove', [FavoriteController::class, 'removeAlbum'])->name('api.favorites.remove-album');
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy'])->name('api.favorites.destroy');

    // Listen History Routes
    Route::post('/listen-history', [ListenHistoryController::class, 'track'])->name('api.listen-history.track');
    Route::get('/listen-history', [ListenHistoryController::class, 'myHistory'])->name('api.listen-history.me');

    // Streaming Playback Routes
    Route::get('/songs/{song}/stream-ticket', [StreamTicketController::class, 'show'])->name('api.streaming.ticket');
    Route::post('/playback/progress', [PlaybackController::class, 'progress'])->name('api.playback.progress');
    Route::post('/playback/complete', [PlaybackController::class, 'complete'])->name('api.playback.complete');
    Route::get('/playback/recent', [PlaybackController::class, 'recentlyPlayed'])->name('api.playback.recent');
    Route::get('/songs/{song}/lyrics', [LyricsController::class, 'show'])->name('api.songs.lyrics');

    // Playlist Routes
    Route::get('/playlists', [PlaylistController::class, 'index'])->name('api.playlists.index');
    Route::post('/playlists', [PlaylistController::class, 'store'])->name('api.playlists.store');
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('api.playlists.destroy');
    Route::get('/playlists/{playlist}/songs', [PlaylistController::class, 'songs'])->name('api.playlists.songs');
    Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'addSong'])->name('api.playlists.add-song');
    Route::delete('/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong'])->name('api.playlists.remove-song');
    Route::get('/playlists/{playlist}/songs/{song}/exists', [PlaylistController::class, 'hasSong'])->name('api.playlists.has-song');

    // Product Routes (protected)
    Route::middleware('role:admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store'])
            ->name('api.products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->name('api.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->name('api.products.destroy');
    });
});

Route::get('/stream/{song}/{quality}', [AudioStreamController::class, 'stream'])
    ->whereIn('quality', ['128', '320'])
    ->middleware(['throttle:120,1', 'require.range'])
    ->name('api.streaming.audio');

Route::get('/songs/{song}/signed-url', [StreamTicketController::class, 'signedUrl'])
    ->name('api.streaming.signed-url.public');

Route::get('/songs/{song}/cover-url', [StreamTicketController::class, 'coverUrl'])
    ->name('api.streaming.cover-url.public');

Route::get('/albums/{album}/cover-url', [AlbumController::class, 'coverUrl'])
    ->name('api.albums.cover-url.public');

// Dev helper: return a temporary Sanctum token for the first artist's user
if (app()->environment('local') || app()->environment('development') || env('APP_DEBUG')) {
    Route::get('/dev/token-first-artist', function () {
        // Ensure there is a user to attach an artist to
        $user = App\Models\User::first();
        if (! $user) {
            $user = App\Models\User::factory()->create([
                'name' => 'Dev User',
                'email' => 'dev@example.local',
                'password' => bcrypt('password'),
            ]);
        }

        // Ensure the user has an artist record
        $artist = App\Models\Artist::where('user_id', $user->id)->first();
        if (! $artist) {
            $artist = App\Models\Artist::create([
                'user_id' => $user->id,
                'name' => $user->name ?: 'Dev Artist',
                'slug' => 'dev-artist',
            ]);
        }

        $token = $user->createToken('dev-cli')->plainTextToken;
        return response()->json(['token' => $token, 'user_id' => $user->id, 'artist_id' => $artist->id]);
    });
}

