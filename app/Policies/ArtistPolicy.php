<?php

namespace App\Policies;

use App\Models\Artist;
use App\Models\User;

class ArtistPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function view(?User $user, Artist $artist): bool
    {
        return (bool) $artist->is_active || ($user && $this->owns($user, $artist));
    }

    public function update(User $user, Artist $artist): bool
    {
        return $this->owns($user, $artist);
    }

    public function verify(User $user, Artist $artist): bool
    {
        return $user->isAdmin();
    }

    protected function owns(User $user, Artist $artist): bool
    {
        return $artist->user_id === $user->id;
    }
}
