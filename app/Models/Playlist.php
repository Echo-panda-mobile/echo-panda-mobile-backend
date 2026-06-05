<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'image_url',
        'cover_key',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'playlist_song')
            ->withPivot(['id', 'added_at'])
            ->withTimestamps();
    }

    public static function hasColumn(string $column): bool
    {
        static $cache = [];

        if (! array_key_exists($column, $cache)) {
            $cache[$column] = Schema::hasColumn('playlists', $column);
        }

        return (bool) $cache[$column];
    }

    public function coverSource(): ?string
    {
        if (self::hasColumn('cover_key') && $this->cover_key) {
            return $this->cover_key;
        }

        if (self::hasColumn('image_url') && $this->image_url) {
            return $this->image_url;
        }

        return null;
    }

    public static function keyFromUrlOrKey(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            return ltrim($value, '/');
        }

        $path = parse_url($value, PHP_URL_PATH);

        return $path ? ltrim(rawurldecode($path), '/') : null;
    }

    public static function resolveCoverUrl(?string $coverSource): ?string
    {
        if (! $coverSource) {
            return null;
        }

        if (preg_match('#^https?://#i', $coverSource)) {
            if (str_contains($coverSource, 'amazonaws.com')) {
                $path = parse_url($coverSource, PHP_URL_PATH);
                $coverSource = ltrim((string) $path, '/');
            } else {
                return $coverSource;
            }
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl(ltrim($coverSource, '/'), now()->addMinutes(60));
        } catch (\Exception) {
            return $disk->url(ltrim($coverSource, '/'));
        }
    }

    public function resolvedImageUrl(): ?string
    {
        return self::resolveCoverUrl($this->coverSource());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function applyCoverAttributes(array &$attributes): void
    {
        $coverKey = $attributes['cover_key'] ?? null;
        $imageUrl = $attributes['image_url'] ?? null;
        $normalizedKey = $coverKey ?: self::keyFromUrlOrKey($imageUrl);

        if (self::hasColumn('cover_key')) {
            $attributes['cover_key'] = $normalizedKey;
            unset($attributes['image_url']);

            return;
        }

        if (self::hasColumn('image_url')) {
            $attributes['image_url'] = $normalizedKey ?: $imageUrl;
            unset($attributes['cover_key']);
        }
    }

    public function assignCoverKey(string $key): void
    {
        if (self::hasColumn('cover_key')) {
            $this->cover_key = $key;
        } elseif (self::hasColumn('image_url')) {
            $this->image_url = $key;
        }
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cover_key' => self::hasColumn('cover_key') ? $this->cover_key : null,
            'image_url' => $this->resolvedImageUrl(),
            'songs_count' => $this->songs_count ?? $this->songs()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
