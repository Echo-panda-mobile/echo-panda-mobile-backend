<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Genre;

class GenrePolicy
{
    public function viewAny(?User $user)
    {
        return $user !== null;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Genre $genre)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Genre $genre)
    {
        return $user->role === 'admin';
    }
}
