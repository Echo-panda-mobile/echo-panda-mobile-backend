<?php

namespace App\Policies;

use App\Models\Song;
use App\Models\User;

class SongPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function view(?User $user, Song $song): bool
    {
        // public songs are viewable by anyone
        return (bool) $song->is_active || ($user && $this->owns($user, $song));
    }

    public function create(User $user): bool
    {
        return $user->isArtistOrPublicer();
    }

    public function update(User $user, Song $song): bool
    {
        return $this->owns($user, $song);
    }

    public function delete(User $user, Song $song): bool
    {
        return $this->owns($user, $song);
    }

    public function publish(User $user, Song $song): bool
    {
        return $this->owns($user, $song);
    }

    protected function owns(User $user, Song $song): bool
    {
        $artist = $user->artist;

        return $artist !== null && (int) $song->artist_id === (int) $artist->id;
    }
}
