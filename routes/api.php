<?php

use App\Http\Controllers\Api\Admin\AdminArtistController;
use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\Artist\AnalyticsController;
use App\Http\Controllers\Api\Artist\UploadController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\Streaming\AudioStreamController;
use App\Http\Controllers\Api\Streaming\LyricsController;
use App\Http\Controllers\Api\Streaming\PlaybackController;
use App\Http\Controllers\Api\Streaming\StreamTicketController;
use App\Http\Controllers\Api\ListenHistoryController;
use App\Http\Controllers\Api\Mobile\MbArtistController;
use App\Http\Controllers\Api\Mobile\MbFavoriteController;
use App\Http\Controllers\Api\Mobile\MbGenreController;
use App\Http\Controllers\Api\Mobile\MbPlaybackController;
use App\Http\Controllers\Api\Mobile\MbTagController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\SongController;
use App\Http\Controllers\Api\CatalogImageUploadController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserUploadController;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/firebase/session', [AuthController::class, 'firebaseLogin'])
    ->middleware('firebase.auth')
    ->name('api.firebase.session');

// Public Routes (no authentication required)
Route::get('/genres', [GenreController::class, 'index'])->name('api.genres.index');
Route::get('/tags', [TagController::class, 'index'])->name('api.tags.index');

// Public Album and Song Routes (readable by everyone)
Route::get('/albums', [AlbumController::class, 'index'])->name('api.albums.index');
Route::get('/albums/new-releases-today', [AlbumController::class, 'newReleasesToday'])->name('api.albums.new-releases-today');
Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('api.albums.show');
Route::get('/albums/{albumId}/songs', [SongController::class, 'getByAlbum'])->name('api.albums.songs');
Route::get('/albums/{album}/cover-url', [AlbumController::class, 'coverUrl'])->name('api.albums.cover-url');
Route::get('/songs', [SongController::class, 'index'])->name('api.songs.index');
Route::get('/songs/{song}', [SongController::class, 'show'])->name('api.songs.show');
Route::get('/recommendations/similar/{song}', [RecommendationController::class, 'similar'])->name('api.recommendations.similar');
Route::get('/recommendations/cold-start', [RecommendationController::class, 'coldStart'])->name('api.recommendations.cold-start');
Route::get('/stats/most-played-songs', [ListenHistoryController::class, 'mostPlayedSongs'])->name('api.stats.most-played-songs');
Route::get('/stats/most-played-albums', [ListenHistoryController::class, 'mostPlayedAlbums'])->name('api.stats.most-played-albums');
Route::get('/genres', [\App\Http\Controllers\Api\GenreController::class, 'index'])->name('api.genres.index');
Route::get('/artists', [\App\Http\Controllers\Api\Artist\ArtistController::class, 'index'])->name('api.artists.index');
Route::get('/artists/popular', [MbArtistController::class, 'popular'])->name('api.artists.popular');
Route::get('/artists/{artist}/image-url', [\App\Http\Controllers\Api\Artist\ArtistController::class, 'imageUrl'])->name('api.artists.image-url');
Route::get('/users/{user}/image-url', [UserController::class, 'imageUrl'])->name('api.users.image-url');
Route::get('/genres/{genre}/image-url', [GenreController::class, 'imageUrl'])->name('api.genres.image-url');
Route::get('/tags/{tag}/image-url', [TagController::class, 'imageUrl'])->name('api.tags.image-url');
Route::get('/mb/genres', [MbGenreController::class, 'index'])->name('api.mb.genres.index');
Route::get('/mb/tags', [MbTagController::class, 'index'])->name('api.mb.tags.index');

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User Authentication Routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    // User Management (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users/by-role', [AuthController::class, 'usersByRole'])
            ->name('api.users.by-role');
        Route::post('/admin/artists', [AdminArtistController::class, 'store'])
            ->name('api.admin.artists.store');

        Route::post('/genres/{genre}/image/presign', [CatalogImageUploadController::class, 'presignGenre'])
            ->name('api.genres.image.presign');
        Route::post('/genres/{genre}/image', [CatalogImageUploadController::class, 'mediaGenre'])
            ->name('api.genres.image.store');
        Route::post('/tags/{tag}/image/presign', [CatalogImageUploadController::class, 'presignTag'])
            ->name('api.tags.image.presign');
        Route::post('/tags/{tag}/image', [CatalogImageUploadController::class, 'mediaTag'])
            ->name('api.tags.image.store');
    });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::post('/upload/user-image/presign', [UserUploadController::class, 'presign'])
        ->name('api.upload.user-image.presign');
    Route::post('/upload/user-image', [UserUploadController::class, 'media'])
        ->name('api.upload.user-image.store');
    Route::get('/profile/favorite-songs', [ProfileController::class, 'getFavoriteSongs'])->name('api.profile.favorite-songs');
    Route::get('/profile/favorite-albums', [ProfileController::class, 'getFavoriteAlbums'])->name('api.profile.favorite-albums');
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('api.recommendations.index');
    Route::post('/recommendations/events', [RecommendationController::class, 'trackEvent'])->name('api.recommendations.events.track');

    // Artist/Publisher Routes
    Route::middleware('role:artist,publicer,admin')->group(function () {
        Route::post('/upload/media/presign', [UploadController::class, 'presignMedia'])
            ->name('api.upload.media.presign');

        Route::post('/upload/media', [UploadController::class, 'media'])
            ->name('api.upload.media.store');

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

    // Mobile-only routes (MB prefix — do not change web-facing endpoints above)
    Route::prefix('mb')->name('api.mb.')->group(function () {
        Route::get('/favorites', [MbFavoriteController::class, 'index'])->name('favorites.index');
        Route::get('/playback/recent', [MbPlaybackController::class, 'recent'])->name('playback.recent');
        Route::get('/artists/popular', [MbArtistController::class, 'popular'])->name('artists.popular');
        Route::get('/artists/random', [MbArtistController::class, 'random'])->name('artists.random');
    });

    // Listen History Routes
    Route::post('/listen-history', [ListenHistoryController::class, 'track'])->name('api.listen-history.track');
    Route::get('/listen-history', [ListenHistoryController::class, 'myHistory'])->name('api.listen-history.me');

    // Reporting Routes
    Route::post('/reports', [\App\Http\Controllers\Api\ReportController::class, 'store'])->name('api.reports.store');

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

    // AI Prompted Playlists
    Route::prefix('ai-playlists')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\AiPlaylistController::class, 'index']);
        Route::post('/generate', [\App\Http\Controllers\Api\AiPlaylistController::class, 'generate']);
        Route::get('/{playlist}', [\App\Http\Controllers\Api\AiPlaylistController::class, 'show']);
        Route::delete('/{playlist}', [\App\Http\Controllers\Api\AiPlaylistController::class, 'destroy']);
    });
});

require __DIR__.'/api/mobile-admin.php';

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
