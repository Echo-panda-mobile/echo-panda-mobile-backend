<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tag;

class TagPolicy
{
    public function viewAny(?User $user)
    {
        return $user !== null;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Tag $tag)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Tag $tag)
    {
        return $user->role === 'admin';
    }
}
