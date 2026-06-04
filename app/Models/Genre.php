<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'is_active', 'show_as_row'];

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
}
