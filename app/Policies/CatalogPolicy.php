<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Catalog (categories, industries, products):
 * every staff member can look, only owner/admin can change.
 */
abstract class CatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $record): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Model $record): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Model $record): bool
    {
        return $user->isAdmin();
    }

    public function restoreAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return $user->isOwner();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function reorder(User $user): bool
    {
        return $user->isAdmin();
    }
}
