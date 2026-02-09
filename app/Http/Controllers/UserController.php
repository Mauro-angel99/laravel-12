<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\WorkPhaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct(
        private WorkPhaseService $workPhaseService
    ) {}

    /**
     * Get list of available users (with caching).
     */
    public function index(): JsonResponse
    {
        try {
            $users = $this->workPhaseService->getAvailableUsers();
            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Users fetch error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Errore nel caricamento degli utenti'
            ], 500);
        }
    }
}