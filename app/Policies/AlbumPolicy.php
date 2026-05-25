<?php

namespace App\Policies;

use App\Models\Album;
use App\Models\User;

class AlbumPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function view(?User $user, Album $album): bool
    {
        return (bool) $album->release_date || ($user && $this->owns($user, $album));
    }

    public function create(User $user): bool
    {
        return $user->isArtistOrPublicer();
    }

    public function update(User $user, Album $album): bool
    {
        return $this->owns($user, $album);
    }

    public function delete(User $user, Album $album): bool
    {
        return $this->owns($user, $album);
    }

    protected function owns(User $user, Album $album): bool
    {
        $artist = $user->artist;

        return $artist !== null && (int) $album->artist_id === (int) $artist->id;
    }
}
