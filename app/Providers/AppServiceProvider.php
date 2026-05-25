<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Song;
use App\Models\Album;
use App\Models\Artist;
use App\Policies\SongPolicy;
use App\Policies\AlbumPolicy;
use App\Policies\ArtistPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        // Register application policies
        Gate::policy(Song::class, SongPolicy::class);
        Gate::policy(Album::class, AlbumPolicy::class);
        Gate::policy(Artist::class, ArtistPolicy::class);
    }
}
