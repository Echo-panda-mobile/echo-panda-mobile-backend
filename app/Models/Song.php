<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Song extends Model
{
    /** @use HasFactory<\Database\Factories\SongFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'album_id',
        'artist_id',
        'title',
        'slug',
        'artist',
        'duration',
        'bitrate',
        'file_size_bytes',
        'mime_type',
        'track_number',
        'lyrics',
        'default_quality',
        'is_active',
        'play_count',
        'published_at',
        'original_key',
        'cover_key',
        'preview_key',
        'waveform_json',
        'processing_status',
        'processing_error',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the album that owns the song.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Get the artist that owns the song.
     */
    public function artistModel(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    /**
     * Get all favorites for this song.
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Get listen history for this song.
     */
    public function listenHistory()
    {
        return $this->hasMany(ListenHistory::class);
    }

    /**
     * Get event-level play history entries for this song.
     */
    public function playHistory(): HasMany
    {
        return $this->hasMany(PlayHistory::class);
    }

    /**
     * Get stream logs for this song.
     */
    public function streamLogs(): HasMany
    {
        return $this->hasMany(StreamLog::class);
    }

    /**
     * Get synced lyrics for this song.
     */
    public function lyric(): HasOne
    {
        return $this->hasOne(Lyric::class);
    }

    /**
     * Get ratings for this song.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
