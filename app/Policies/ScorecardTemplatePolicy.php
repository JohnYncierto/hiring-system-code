<?php

namespace App\Policies;

use App\Models\ScorecardTemplate;
use App\Models\User;

class ScorecardTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, ScorecardTemplate $template): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, ScorecardTemplate $template): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, ScorecardTemplate $template): bool
    {
        return $user->hasRole('admin');
    }
}
