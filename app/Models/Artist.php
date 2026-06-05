<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

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
        'slug',
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
    public function getImageUrlAttribute(?string $value): ?string
    {
        $path = $value ?? $this->attributes['cover_image_url'] ?? null;
        if (!$path) return null;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Try public disk first
        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($path)) {
            return $publicDisk->url($path);
        }

        // Fallback to S3 with signed URL
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
     * Get the cover image URL with multi-disk support.
     */
    public function getCoverImageUrlAttribute(?string $value): ?string
    {
        if (!$value) return null;

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Try public disk first
        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($value)) {
            return $publicDisk->url($value);
        }

        // Fallback to S3 with signed URL
        try {
            /** @var FilesystemAdapter $s3Disk */
            $s3Disk = Storage::disk('s3');

            return $s3Disk->temporaryUrl($value, now()->addMinutes(60));
        } catch (\Exception $e) {
            /** @var FilesystemAdapter $s3Disk */
            $s3Disk = Storage::disk('s3');

            return $s3Disk->url($value);
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
