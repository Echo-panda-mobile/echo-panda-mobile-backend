<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Share extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'platform',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owning targetable model.
     */
    public function target()
    {
        return match ($this->target_type) {
            'song' => $this->belongsTo(Song::class, 'target_id'),
            'album' => $this->belongsTo(Album::class, 'target_id'),
            'artist' => $this->belongsTo(Artist::class, 'target_id'),
            'playlist' => $this->belongsTo(Playlist::class, 'target_id'),
            default => null,
        };
    }
}
