<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\WorkParameter;

class WorkPhaseService
{
    /**
     * Get available users with caching.
     */
    public function getAvailableUsers(): array
    {
        return Cache::remember('users_available', 300, function () {
            return User::select('id', 'name', 'email')
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['operator', 'manager', 'admin']);
                })
                ->orderBy('name')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get work parameters with caching.
     */
    public function getWorkParameters(): array
    {
        return Cache::remember('work_parameters_all', 600, function () {
            return WorkParameter::orderBy('name')->get()->toArray();
        });
    }

    /**
     * Get a single work parameter with caching.
     */
    public function getWorkParameter(int $id): ?WorkParameter
    {
        return Cache::remember("work_parameter_{$id}", 600, function () use ($id) {
            return WorkParameter::find($id);
        });
    }

    /**
     * Clear all work phase related caches.
     */
    public function clearCache(): void
    {
        Cache::forget('users_available');
        Cache::forget('work_parameters_all');
        
        // Clear individual parameter caches
        $parameterIds = WorkParameter::pluck('id');
        foreach ($parameterIds as $id) {
            Cache::forget("work_parameter_{$id}");
        }
    }

    /**
     * Clear cache for a specific work parameter.
     */
    public function clearParameterCache(int $id): void
    {
        Cache::forget("work_parameter_{$id}");
        Cache::forget('work_parameters_all');
    }
}
