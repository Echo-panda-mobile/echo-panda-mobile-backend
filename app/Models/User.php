<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER = 'user';
    public const ROLE_ARTIST = 'artist';
    public const ROLE_PUBLICER = 'publicer';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isArtistOrPublicer(): bool
    {
        return in_array($this->role, [self::ROLE_ARTIST, self::ROLE_PUBLICER], true);
    }

    /**
     * Get all favorites for the user.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get user's listen history.
     */
    public function listenHistory()
    {
        return $this->hasMany(ListenHistory::class);
    }

    /**
     * Get event-level play history for this user.
     */
    public function playHistory()
    {
        return $this->hasMany(PlayHistory::class);
    }

    /**
     * Get user's song ratings.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get user's genre preferences.
     */
    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * Get artists this user is following.
     */
    public function following()
    {
        return $this->hasMany(ArtistFollower::class, 'user_id');
    }

    /**
     * Get users following this artist.
     */
    public function followers()
    {
        return $this->hasMany(ArtistFollower::class, 'artist_user_id');
    }

    /**
     * Get the artist profile for this user.
     */
    public function artist(): HasOne
    {
        return $this->hasOne(Artist::class);
    }

    public function roleRedirectTarget(): string
    {
        if ($this->isAdmin()) {
            return config('app.url').'/admin';
        }

        if ($this->isArtistOrPublicer()) {
            return env('FRONTEND_ARTIST_DASHBOARD_URL', '/admin/dashboard');
        }

        return env('FRONTEND_HOME_URL', '/');
    }
}
