<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Artist;
use App\Models\Song;
use App\Models\Album;
use App\Models\Report;
use App\Models\Genre;
use App\Models\FeaturedItem;

use App\Policies\ArtistPolicy;
use App\Policies\SongPolicy;
use App\Policies\AlbumPolicy;
use App\Policies\ReportPolicy;
use App\Policies\GenrePolicy;
use App\Policies\FeaturedItemPolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Artist::class => ArtistPolicy::class,
        Song::class => SongPolicy::class,
        Album::class => AlbumPolicy::class,
        Report::class => ReportPolicy::class,
        Genre::class => GenrePolicy::class,
        FeaturedItem::class => FeaturedItemPolicy::class,
        \App\Models\User::class => UserPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Global before: admins bypass policies
        Gate::before(function ($user, $ability) {
            if ($user && $user->role === 'admin') {
                return true;
            }
        });
    }
}
