<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Album extends Model
{
    /** @use HasFactory<\Database\Factories\AlbumFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['cover_url'];

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
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the cover URL with multi-disk support.
     */
    public function getCoverUrlAttribute($value)
    {
        $path = $this->attributes['cover_key'] ?? $value ?? null;
        if (!$path) return null;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Try public disk first
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }

        // Fallback to S3 with signed URL
        try {
            return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(60));
        } catch (\Exception $e) {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
        }
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

    /**
     * Get moderation reports for this album.
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
