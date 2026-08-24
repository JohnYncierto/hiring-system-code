<?php

namespace App\Policies;

use App\Models\PipelineTemplate;
use App\Models\User;

class PipelineTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, PipelineTemplate $template): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, PipelineTemplate $template): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, PipelineTemplate $template): bool
    {
        return $user->hasRole('admin');
    }
}
