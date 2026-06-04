<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
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
}