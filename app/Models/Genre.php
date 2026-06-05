<?php

namespace App\Models;

use App\Models\Concerns\HasS3CatalogImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genre extends Model
{
    use HasFactory;
    use HasS3CatalogImage;

    protected $fillable = ['name', 'slug', 'image_url', 'is_active', 'show_as_row'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_as_row' => 'boolean',
        ];
    }

    /**
     * Get the songs for the genre.
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'category_id');
    }

    protected function catalogImageFolder(): string
    {
        return 'images/genre-images';
    }

    protected static function booted(): void
    {
        static::deleting(function (Genre $genre): void {
            $genre->deleteStoredImage();
        });
    }
}
