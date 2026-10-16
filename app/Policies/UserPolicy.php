<?php

namespace App\Policies;

use App\Models\User;

/** Only the owner manages staff accounts. Nobody can delete themselves. */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isOwner();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isOwner() && $user->id !== $model->id;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
