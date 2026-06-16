<?php

namespace App\Policies;

use App\Models\Redirect;
use App\Models\User;

class RedirectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Redirect $redirect): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Redirect $redirect): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Redirect $redirect): bool
    {
        return $user->role === 'admin';
    }
}
