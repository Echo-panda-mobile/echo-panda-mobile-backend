<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Song extends Model
{
    /** @use HasFactory<\Database\Factories\SongFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['cover_url', 'audio_url'];

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
        'category_id',
        'tag_id',
        'mood',
        'song_type',
        'bpm',
        'is_explicit',
        'featured_artists',
        'default_quality',
        'is_active',
        'play_count',
        'published_at',
        'original_key',
        'variant_key_128',
        'variant_key_320',
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
     * Get the cover image URL with multi-disk support.
     */
    public function getCoverUrlAttribute()
    {
        $path = $this->attributes['cover_key'] ?? null;
        if (!$path) return null;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($path)) {
            return $publicDisk->url($path);
        }

        try {
            /** @var FilesystemAdapter $s3Disk */
            $s3Disk = Storage::disk('s3');
            return $s3Disk->temporaryUrl($path, now()->addMinutes(60));
        } catch (\Exception $e) {
            /** @var FilesystemAdapter $s3Disk */
            $s3Disk = Storage::disk('s3');
            return $s3Disk->url($path);
        }
    }

    /**
     * Get the audio source URL with multi-disk support.
     */
    public function getAudioUrlAttribute()
    {
        $path = $this->attributes['original_key']
            ?? $this->attributes['variant_key_320']
            ?? $this->attributes['variant_key_128']
            ?? null;

        if (!$path) return null;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }

        try {
            return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(60));
        } catch (\Exception $e) {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
        }
    }

    /**
     * Get the genre/category for the song.
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'category_id');
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
     * Get the tag assigned to this song (optional).
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
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

    /**
     * Get moderation reports for this song.
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
