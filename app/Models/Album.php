<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    /** @use HasFactory<\Database\Factories\AlbumFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'artist_id',
        'title',
        'slug',
        'artist',
        'release_date',
        'description',
        'release_status',
        'scheduled_at',
        'cover_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Keep the legacy cover_url attribute available as an alias for cover_key.
     */
    public function getCoverUrlAttribute()
    {
        return $this->attributes['cover_key'] ?? $this->attributes['cover_url'] ?? null;
    }

    /**
     * Mirror writes to cover_url onto cover_key for backward compatibility.
     */
    public function setCoverUrlAttribute($value): void
    {
        $this->attributes['cover_key'] = $value;
        $this->attributes['cover_url'] = $value;
    }

    /**
     * Get the songs for the album.
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    /**
     * Get the artist that owns the album.
     */
    public function artistModel(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    /**
     * Get all favorites for this album.
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}
