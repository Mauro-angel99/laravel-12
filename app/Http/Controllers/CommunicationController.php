<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommunicationController extends Controller
{
    private const WORK_PHASE_FIELDS = ['opart_padre', 'opart', 'flass', 'fllav', 'idopr'];

    private const WORK_PHASE_RULES = [
        'opart_padre' => 'nullable|string|max:50',
        'opart' => 'nullable|string|max:50',
        'flass' => 'nullable|string|max:50',
        'fllav' => 'nullable|string|max:50',
        'idopr' => 'nullable|string|max:50',
    ];

    /**
     * Pagina gestione comunicazioni (solo moderatori)
     */
    public function manage()
    {
        return view('communications.manage');
    }

    /**
     * Pagina comunicazioni ricevute dall'utente
     */
    public function inbox()
    {
        return view('communications.inbox');
    }

    /**
     * Comunicazioni di una fase di lavoro (tab Comunicazione della modal)
     */
    public function forWorkPhase(Request $request): JsonResponse
    {
        $validated = $request->validate(self::WORK_PHASE_RULES);

        $query = Communication::query();

        foreach (self::WORK_PHASE_FIELDS as $field) {
            if (!empty($validated[$field])) {
                $query->where($field, $validated[$field]);
            } else {
                $query->whereNull($field);
            }
        }

        $user = Auth::user();

        $data = $query->orderByDesc('created_at')->get()
            ->map(fn (Communication $communication) => $this->formatForWorkPhase($communication, $user));

        return response()->json(['data' => $data]);
    }

    /**
     * Invia una nuova comunicazione su una fase di lavoro
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            ...self::WORK_PHASE_RULES,
            'message' => 'required|string|max:2000',
        ]);

        try {
            $user = Auth::user();

            $communication = Communication::create([
                ...$validated,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            Log::info('Communication created', [
                'communication_id' => $communication->id,
                'idopr' => $communication->idopr,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comunicazione inviata con successo',
                'data' => $this->formatForWorkPhase($communication, $user),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating communication: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'invio della comunicazione',
            ], 500);
        }
    }

    /**
     * Modifica il testo di una comunicazione (autore o moderatore)
     */
    public function update(Request $request, Communication $communication): JsonResponse
    {
        $user = Auth::user();

        if (!$communication->canBeManagedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non hai i permessi per modificare questa comunicazione',
            ], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        try {
            $communication->update($validated);

            Log::info('Communication updated', [
                'communication_id' => $communication->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comunicazione modificata con successo',
                'data' => $this->formatForWorkPhase($communication, $user),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating communication: ' . $e->getMessage(), [
                'communication_id' => $communication->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la modifica della comunicazione',
            ], 500);
        }
    }

    /**
     * Elimina una comunicazione (autore o moderatore)
     */
    public function destroy(Communication $communication): JsonResponse
    {
        $user = Auth::user();

        if (!$communication->canBeManagedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non hai i permessi per eliminare questa comunicazione',
            ], 403);
        }

        try {
            $communication->delete();

            Log::info('Communication deleted', [
                'communication_id' => $communication->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comunicazione eliminata con successo',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting communication: ' . $e->getMessage(), [
                'communication_id' => $communication->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione della comunicazione',
            ], 500);
        }
    }

    /**
     * Elenco paginato di tutte le comunicazioni (solo moderatori)
     */
    public function manageList(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opart_padre' => 'nullable|string|max:50',
            'opart' => 'nullable|string|max:50',
            'fllav' => 'nullable|string|max:50',
            'idopr' => 'nullable|string|max:50',
            'user_name' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'unassigned' => 'nullable|boolean',
        ]);

        $query = Communication::with('recipients:id,name')->orderByDesc('created_at');

        foreach (['opart_padre', 'opart', 'fllav', 'idopr', 'user_name', 'message'] as $field) {
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

        if (!empty($validated['unassigned'])) {
            $query->doesntHave('recipients');
        }

        $paginator = $query->paginate(20);

        $data = collect($paginator->items())->map(fn (Communication $communication) => [
            ...$this->formatBase($communication),
            'recipients' => $communication->recipients->map(fn ($recipient) => [
                'id' => $recipient->id,
                'name' => $recipient->name,
                'read_at' => $recipient->pivot->read_at
                    ? Carbon::parse($recipient->pivot->read_at)->timezone('Europe/Rome')->format('d/m/Y H:i')
                    : null,
            ])->values(),
        ]);

        return response()->json([
            'data' => $data,
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    /**
     * Assegna la comunicazione agli utenti destinatari (solo moderatori)
     */
    public function assign(Request $request, Communication $communication): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'present|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            // sync mantiene read_at per i destinatari già assegnati
            $communication->recipients()->sync($validated['user_ids']);

            Log::info('Communication recipients assigned', [
                'communication_id' => $communication->id,
                'recipients' => $validated['user_ids'],
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Destinatari assegnati con successo',
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning communication recipients: ' . $e->getMessage(), [
                'communication_id' => $communication->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'assegnazione dei destinatari',
            ], 500);
        }
    }

    /**
     * Elenco paginato delle comunicazioni assegnate all'utente loggato
     */
    public function inboxList(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unread' => 'nullable|boolean',
        ]);

        $query = Auth::user()->receivedCommunications()->orderByDesc('communications.created_at');

        if (!empty($validated['unread'])) {
            $query->wherePivotNull('read_at');
        }

        $paginator = $query->paginate(20);

        $data = collect($paginator->items())->map(fn (Communication $communication) => [
            ...$this->formatBase($communication),
            'read_at' => $communication->pivot->read_at
                ? Carbon::parse($communication->pivot->read_at)->timezone('Europe/Rome')->format('d/m/Y H:i')
                : null,
        ]);

        return response()->json([
            'data' => $data,
            'pagination' => $this->paginationMeta($paginator),
            'unread_count' => $this->unreadCountFor(Auth::user()),
        ]);
    }

    /**
     * Numero di comunicazioni non lette dell'utente loggato (badge nel menu)
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread_count' => $this->unreadCountFor(Auth::user()),
        ]);
    }

    /**
     * Segna come letta una comunicazione ricevuta
     */
    public function markRead(Communication $communication): JsonResponse
    {
        $user = Auth::user();

        $updated = $user->receivedCommunications()
            ->updateExistingPivot($communication->id, ['read_at' => now()]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Comunicazione non assegnata a questo utente',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'read_at' => now()->timezone('Europe/Rome')->format('d/m/Y H:i'),
            'unread_count' => $this->unreadCountFor($user),
        ]);
    }

    private function unreadCountFor($user): int
    {
        return $user->receivedCommunications()->wherePivotNull('read_at')->count();
    }

    private function formatBase(Communication $communication): array
    {
        return [
            'id' => $communication->id,
            'opart_padre' => $communication->opart_padre,
            'opart' => $communication->opart,
            'flass' => $communication->flass,
            'fllav' => $communication->fllav,
            'idopr' => $communication->idopr,
            'message' => $communication->message,
            'user_name' => $communication->user_name,
            'sent_at' => $communication->created_at?->timezone('Europe/Rome')->format('d/m/Y H:i'),
            'edited' => $communication->updated_at?->gt($communication->created_at) ?? false,
        ];
    }

    private function formatForWorkPhase(Communication $communication, $user): array
    {
        return [
            ...$this->formatBase($communication),
            'can_manage' => $communication->canBeManagedBy($user),
        ];
    }

    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem() ?? 0,
            'to' => $paginator->lastItem() ?? 0,
        ];
    }
}
