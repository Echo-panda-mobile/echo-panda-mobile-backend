<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FeaturedItem;

class FeaturedItemPolicy
{
    public function viewAny(?User $user)
    {
        return $user !== null;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, FeaturedItem $item)
    {
        return $user->role === 'admin';
    }
}
