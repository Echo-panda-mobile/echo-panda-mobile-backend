<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $table = 'user_preferences';

    protected $fillable = [
        'user_id',
        'preference_type',
        'preference_value',
        'genre',
        'preference_score',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getResolvedTypeAttribute(): string
    {
        return $this->preference_type ?: 'genre';
    }

    public function getResolvedValueAttribute(): string
    {
        if (! empty($this->preference_value)) {
            return (string) $this->preference_value;
        }

        return (string) ($this->genre ?? '');
    }
}
