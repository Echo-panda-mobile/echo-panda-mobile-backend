<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeaturedItem extends Model
{
    use HasFactory;

    protected $fillable = ['item_type', 'item_id', 'priority', 'meta'];

    protected $casts = ['meta' => 'array'];
}
