<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Report;

class ReportPolicy
{
    public function viewAny(?User $user)
    {
        return $user !== null;
    }

    public function view(?User $user, Report $report)
    {
        return true;
    }

    public function delete(User $user, Report $report)
    {
        return $user->role === 'admin';
    }
}
