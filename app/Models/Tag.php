<?php

namespace App\Models;

use App\Models\Concerns\HasS3CatalogImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
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


    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    protected function catalogImageFolder(): string
    {
        return 'images/tag-images';
    }

    protected static function booted(): void
    {
        static::deleting(function (Tag $tag): void {
            $tag->deleteStoredImage();
        });
    }
}
