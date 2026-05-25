<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistFollower extends Model
{
    use HasFactory;

    protected $table = 'artist_followers';

    protected $fillable = [
        'user_id',
        'artist_user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function follower()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_user_id');
    }
}
