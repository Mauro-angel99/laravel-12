<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkPhaseAssignment;

class WorkPhasePolicy
{
    /**
     * Determine if the user can view any work phases.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'operator']);
    }

    /**
     * Determine if the user can view the work phase assignment.
     */
    public function view(User $user, WorkPhaseAssignment $assignment): bool
    {
        return $user->hasRole('admin') || 
               $user->hasRole('manager') || 
               $assignment->assigned_to === $user->id;
    }

    /**
     * Determine if the user can assign work phases.
     */
    public function assign(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Determine if the user can update the work phase assignment.
     */
    public function update(User $user, WorkPhaseAssignment $assignment): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('manager') && $assignment->assigned_to === $user->id);
    }

    /**
     * Determine if the user can delete the work phase assignment.
     */
    public function delete(User $user, WorkPhaseAssignment $assignment): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can complete the work phase.
     */
    public function complete(User $user, WorkPhaseAssignment $assignment): bool
    {
        return $assignment->assigned_to === $user->id;
    }

    /**
     * Determine if the user can reassign the work phase.
     */
    public function reassign(User $user, WorkPhaseAssignment $assignment): bool
    {
        return $user->hasRole('admin');
    }
}
