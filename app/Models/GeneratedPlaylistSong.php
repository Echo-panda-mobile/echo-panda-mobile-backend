<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedPlaylistSong extends Model
{
    protected $fillable = ['playlist_id', 'song_id', 'position'];
}
