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
        'slug',
        'bio',
        'image_url',
        'cover_image_url',
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
     * Get the image_url, falling back to cover_image_url for backwards compatibility.
     */
    public function getImageUrlAttribute()
    {
        return $this->attributes['image_url'] ?? $this->attributes['cover_image_url'] ?? null;
    }

    /**
     * Set the image_url and also update cover_image_url.
     */
    public function setImageUrlAttribute($value)
    {
        $this->attributes['image_url'] = $value;
        $this->attributes['cover_image_url'] = $value;
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
