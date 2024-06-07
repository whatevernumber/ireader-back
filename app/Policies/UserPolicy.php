<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    // users allowed only to edit their own account, unless they are an admin
    public function update(User $currentUser, User $requestedUser): bool
    {
        return $currentUser->id === $requestedUser->id || $currentUser->is_admin;
    }

    // users allowed only to delete their own account, unless they are an admin
    public function delete(User $user, User $requested_user): bool
    {
        return $user->id === $requested_user->id || $user->is_admin;
    }
}
