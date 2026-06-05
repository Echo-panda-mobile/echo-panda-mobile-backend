<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'song_id',
        'event_type',
        'recommendation_score',
        'recommendation_reason',
    ];

    protected function casts(): array
    {
        return [
            'recommendation_score' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
