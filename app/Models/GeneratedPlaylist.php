<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GeneratedPlaylist extends Model
{
    protected $fillable = ['user_id', 'title', 'prompt', 'extracted_criteria', 'cover_url'];

    protected $casts = [
        'extracted_criteria' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'generated_playlist_songs', 'playlist_id', 'song_id')
            ->withPivot('position')
            ->orderBy('pivot_position');
    }
}
