<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    /**
     * Determine if the user can view any warehouse entries.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tutti gli utenti autenticati possono vedere
    }

    /**
     * Determine if the user can view the warehouse entry.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        return true;
    }

    /**
     * Determine if the user can create warehouse entries.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'warehouse_operator']);
    }

    /**
     * Determine if the user can update the warehouse entry.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'warehouse_operator']);
    }

    /**
     * Determine if the user can delete the warehouse entry.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can update warehouse positions.
     */
    public function updatePosition(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'warehouse_operator']);
    }

    /**
     * Determine if the user can confirm pending items.
     */
    public function confirmPending(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'warehouse_operator']);
    }
}
