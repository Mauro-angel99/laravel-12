<?php

namespace App\Http\Controllers;

use App\Models\ArticleAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArticleAlertController extends Controller
{
    /**
     * Elenco degli alert di un articolo
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cdart' => 'required|string|max:50',
        ]);

        $alerts = ArticleAlert::with('signer:id,name')
            ->where('cdart', $validated['cdart'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $alerts,
        ]);
    }

    /**
     * Crea un nuovo alert per un articolo
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cdart' => 'required|string|max:50',
            'note' => 'required|string|max:2000',
            'signed_by' => 'required|integer|exists:users,id',
        ]);

        try {
            $alert = ArticleAlert::create([
                ...$validated,
                'created_by' => Auth::id(),
            ]);

            Log::info('Article alert created', [
                'alert_id' => $alert->id,
                'cdart' => $alert->cdart,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alert creato con successo',
                'data' => $alert->load('signer:id,name'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating article alert: ' . $e->getMessage(), [
                'cdart' => $validated['cdart'],
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'alert',
            ], 500);
        }
    }

    /**
     * Modifica nota e firma di un alert
     */
    public function update(Request $request, ArticleAlert $alert): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
            'signed_by' => 'required|integer|exists:users,id',
        ]);

        try {
            $alert->update($validated);

            Log::info('Article alert updated', [
                'alert_id' => $alert->id,
                'cdart' => $alert->cdart,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alert modificato con successo',
                'data' => $alert->load('signer:id,name'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating article alert: ' . $e->getMessage(), [
                'alert_id' => $alert->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la modifica dell\'alert',
            ], 500);
        }
    }

    /**
     * Elimina un alert
     */
    public function destroy(ArticleAlert $alert): JsonResponse
    {
        try {
            $alert->delete();

            Log::info('Article alert deleted', [
                'alert_id' => $alert->id,
                'cdart' => $alert->cdart,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alert eliminato con successo',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting article alert: ' . $e->getMessage(), [
                'alert_id' => $alert->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione dell\'alert',
            ], 500);
        }
    }
}
