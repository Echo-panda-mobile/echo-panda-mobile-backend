<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Artist extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'bio',
        'image_url',
        'cover_image_url',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'youtube_url',
        'is_active',
        'verification_status',
        'verification_reason',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the profile image URL with multi-disk support.
     */
    public function getImageUrlAttribute($value)
    {
        $path = $value ?? $this->attributes['cover_image_url'] ?? null;
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
     * Get the cover image URL with multi-disk support.
     */
    public function getCoverImageUrlAttribute($value)
    {
        if (!$value) return null;

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Try public disk first
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($value)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($value);
        }

        // Fallback to S3 with signed URL
        try {
            return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($value, now()->addMinutes(60));
        } catch (\Exception $e) {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($value);
        }
    }

    /**
     * Get the artist albums.
     */
    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    /**
     * Get the artist songs.
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    /**
     * Get the owning user account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
