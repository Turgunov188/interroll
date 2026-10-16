<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

/**
 * Owner/admin: every lead.
 * Sales: leads assigned to them or not yet assigned (so they can pick them up).
 */
class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->isAdmin() || $lead->manager_id === null || $lead->manager_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
