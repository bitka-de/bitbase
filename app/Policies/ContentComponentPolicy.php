<?php

namespace App\Policies;

use App\Models\ContentComponent;
use App\Models\User;

class ContentComponentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, ContentComponent $contentComponent): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, ContentComponent $contentComponent): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, ContentComponent $contentComponent): bool
    {
        return $user->role === 'admin';
    }
}