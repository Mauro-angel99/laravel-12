<?php

namespace App\Http\Controllers;

use App\Models\WorkPhaseAcknowledgement;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkPhaseAcknowledgementController extends Controller
{
    /**
     * Pagina log inizio lavoro (solo admin)
     */
    public function index()
    {
        return view('admin.acknowledgements.index');
    }

    /**
     * Elenco paginato degli inizi lavoro (solo admin)
     */
    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opart_padre' => 'nullable|string|max:50',
            'opart' => 'nullable|string|max:50',
            'fllav' => 'nullable|string|max:50',
            'idopr' => 'nullable|string|max:50',
            'user_name' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $query = WorkPhaseAcknowledgement::query()->orderByDesc('created_at');

        foreach (['opart_padre', 'opart', 'fllav', 'idopr', 'user_name'] as $field) {
            if (!empty($validated[$field])) {
                $query->where($field, 'like', '%' . $validated[$field] . '%');
            }
        }

        // Le date filtro sono in ora italiana, il DB salva in UTC
        if (!empty($validated['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($validated['date_from'], 'Europe/Rome')->startOfDay()->utc());
        }
        if (!empty($validated['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($validated['date_to'], 'Europe/Rome')->endOfDay()->utc());
        }

        $paginator = $query->paginate(20);

        $data = collect($paginator->items())->map(fn (WorkPhaseAcknowledgement $ack) => [
            'id' => $ack->id,
            'opart_padre' => $ack->opart_padre,
            'opart' => $ack->opart,
            'flass' => $ack->flass,
            'fllav' => $ack->fllav,
            'idopr' => $ack->idopr,
            'user_name' => $ack->user_name,
            'viewed_at' => $ack->created_at?->timezone('Europe/Rome')->format('d/m/Y H:i:s'),
        ]);

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
            ],
        ]);
    }

    /**
     * Esporta tutti gli inizi lavoro filtrati (senza paginazione) per XLSX (solo admin)
     */
    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opart_padre' => 'nullable|string|max:50',
            'opart' => 'nullable|string|max:50',
            'fllav' => 'nullable|string|max:50',
            'idopr' => 'nullable|string|max:50',
            'user_name' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $query = WorkPhaseAcknowledgement::query()->orderByDesc('created_at');

        foreach (['opart_padre', 'opart', 'fllav', 'idopr', 'user_name'] as $field) {
            if (!empty($validated[$field])) {
                $query->where($field, 'like', '%' . $validated[$field] . '%');
            }
        }

        if (!empty($validated['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($validated['date_from'], 'Europe/Rome')->startOfDay()->utc());
        }
        if (!empty($validated['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($validated['date_to'], 'Europe/Rome')->endOfDay()->utc());
        }

        $data = $query->get()->map(fn (WorkPhaseAcknowledgement $ack) => [
            'id' => $ack->id,
            'opart_padre' => $ack->opart_padre,
            'opart' => $ack->opart,
            'flass' => $ack->flass,
            'fllav' => $ack->fllav,
            'idopr' => $ack->idopr,
            'user_name' => $ack->user_name,
            'viewed_at' => $ack->created_at?->timezone('Europe/Rome')->format('d/m/Y H:i:s'),
        ]);

        return response()->json(['data' => $data]);
    }

    /**
     * Verifica se una fase di lavoro è già stata presa in visione
     */
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opart_padre' => 'nullable|string|max:50',
            'opart' => 'nullable|string|max:50',
            'flass' => 'nullable|string|max:50',
            'fllav' => 'nullable|string|max:50',
            'idopr' => 'nullable|string|max:50',
        ]);

        $query = WorkPhaseAcknowledgement::query();

        foreach (['opart_padre', 'opart', 'flass', 'fllav', 'idopr'] as $field) {
            if (!empty($validated[$field])) {
                $query->where($field, $validated[$field]);
            } else {
                $query->whereNull($field);
            }
        }

        $ack = $query->orderByDesc('created_at')->first();

        return response()->json([
            'acknowledged' => (bool) $ack,
            'data' => $ack ? [
                'id' => $ack->id,
                'user_name' => $ack->user_name,
                'viewed_at' => $ack->created_at?->timezone('Europe/Rome')->format('d/m/Y H:i:s'),
            ] : null,
        ]);
    }

    /**
     * Registra un inizio lavoro della fase di lavoro
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opart_padre' => 'nullable|string|max:50',
            'opart' => 'nullable|string|max:50',
            'flass' => 'nullable|string|max:50',
            'fllav' => 'nullable|string|max:50',
            'idopr' => 'nullable|string|max:50',
        ]);

        try {
            $user = Auth::user();

            $ack = WorkPhaseAcknowledgement::create([
                ...$validated,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            Log::info('Work phase acknowledgement registered', [
                'ack_id' => $ack->id,
                'idopr' => $ack->idopr,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inizio lavoro registrato con successo',
                'data' => [
                    'id' => $ack->id,
                    'viewed_at' => $ack->created_at->timezone('Europe/Rome')->format('d/m/Y H:i:s'),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error registering work phase acknowledgement: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la registrazione dell\'inizio lavoro',
            ], 500);
        }
    }

    /**
     * Elimina un inizio lavoro (solo admin)
     */
    public function destroy(WorkPhaseAcknowledgement $acknowledgement): JsonResponse
    {
        try {
            $acknowledgement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inizio lavoro eliminato con successo',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting work phase acknowledgement: ' . $e->getMessage(), [
                'ack_id' => $acknowledgement->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Errore durante l'eliminazione dell'inizio lavoro",
            ], 500);
        }
    }
}
