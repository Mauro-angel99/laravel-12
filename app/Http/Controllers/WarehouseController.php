<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehousePosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('warehouse.index');
    }

    // API: lista posizioni con paginazione e filtri
    public function list(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $search = $request->input('search');
        $pending = $request->input('pending');

        $query = WarehousePosition::query()
            ->withCount('warehouses')
            ->having('warehouses_count', '>', 0);

        // Filtro per stato pending
        if ($pending !== null) {
            $pendingBool = (bool) $pending;
            $query->whereHas('warehouses', function ($wq) use ($pendingBool) {
                $wq->where('pending', $pendingBool);
            });
        }

        // Filtri di ricerca
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('warehouse_position', 'like', '%' . $search . '%')
                    ->orWhereHas('warehouses', function ($wq) use ($search) {
                        $wq->where('product_code', 'like', '%' . $search . '%')
                            ->orWhere('production_order', 'like', '%' . $search . '%');
                    });
            });
        }

        $total = $query->count();
        $positions = $query->with('warehouses:id,warehouse_position_id,product_code,production_order,started')
            ->offset($offset)
            ->limit($perPage)
            ->orderBy('warehouse_position')
            ->get();

        // Se il filtro pending è attivo, aggiungi il pending_code alla risposta
        if ($pending !== null && (bool) $pending) {
            $positions->each(function ($position) {
                // Prendi il primo warehouse con pending_code non nullo
                $warehouse = $position->warehouses()->where('pending', true)->first();
                $position->pending_code = $warehouse ? $warehouse->pending_code : null;
            });
        }

        return response()->json([
            'data' => $positions,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ]
        ]);
    }

    // API: ottieni prodotti di una posizione specifica
    public function getPositionProducts($positionId)
    {
        $position = WarehousePosition::findOrFail($positionId);
        $products = $position->warehouses()->with('position')->orderBy('production_order')->get();

        return response()->json([
            'position' => $position,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_position' => 'nullable|string|max:50',
            'heat' => 'nullable|string|max:100',
            'product_code' => 'nullable|string|max:50',
            'product_description' => 'nullable|string|max:255',
            'production_order' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'format' => 'nullable|string|max:100',
            'pending' => 'boolean',
            'pending_code' => 'nullable|string|max:50',
        ]);

        // Validazione logica
        if (!($validated['pending'] ?? false) && empty($validated['warehouse_position'])) {
            return response()->json([
                'success' => false,
                'message' => 'Il campo Posizione è obbligatorio quando non è in attesa.',
                'errors' => ['warehouse_position' => ['Il campo Posizione è obbligatorio.']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Gestione posizione pending con codice univoco
            if (($validated['pending'] ?? false) && empty($validated['warehouse_position'])) {
                $pendingCode = $validated['pending_code'] ?? uniqid('TEMP_', true);
                $validated['warehouse_position'] = 'IN_ATTESA_' . $pendingCode;
            }

            // Trova o crea la posizione (atomico dentro la transazione)
            $position = WarehousePosition::firstOrCreate(
                ['warehouse_position' => $validated['warehouse_position']]
            );

            // Crea la merce nella posizione
            $warehouse = Warehouse::create([
                'warehouse_position_id' => $position->id,
                'heat' => $validated['heat'] ?? null,
                'product_code' => $validated['product_code'] ?? null,
                'production_order' => $validated['production_order'] ?? null,
                'product_description' => $validated['product_description'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'format' => $validated['format'] ?? null,
                'pending' => $validated['pending'] ?? false,
                'pending_code' => $validated['pending_code'] ?? null,
                'created_by' => Auth::id(),
                'received_at' => now(),
            ]);

            DB::commit();

            Log::info('Warehouse item created', [
                'warehouse_id' => $warehouse->id,
                'position' => $validated['warehouse_position'],
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Merce aggiunta al magazzino con successo',
                'data' => $warehouse->load('position')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating warehouse item: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'inserimento della merce'
            ], 500);
        }
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'warehouse_position' => 'required|string|max:50',
            'heat' => 'nullable|string|max:100',
            'product_code' => 'nullable|string|max:50',
            'product_description' => 'nullable|string|max:255',
            'production_order' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'format' => 'nullable|string|max:100',
            'pending' => 'boolean',
            'pending_code' => 'nullable|string|max:50',
            'started' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $oldPositionId = $warehouse->warehouse_position_id;

            // Trova o crea la nuova posizione
            $position = WarehousePosition::firstOrCreate(
                ['warehouse_position' => $validated['warehouse_position']]
            );

            // Aggiorna la merce
            $warehouse->update([
                'warehouse_position_id' => $position->id,
                'heat' => $validated['heat'] ?? null,
                'product_code' => $validated['product_code'],
                'production_order' => $validated['production_order'],
                'product_description' => $validated['product_description'],
                'notes' => $validated['notes'] ?? null,
                'format' => $validated['format'] ?? null,
                'pending' => $validated['pending'] ?? false,
                'pending_code' => $validated['pending_code'] ?? null,
                'started' => $request->boolean('started'),
                'updated_by' => Auth::id(),
            ]);

            // Se la posizione è cambiata, elimina la vecchia se è vuota
            if ($oldPositionId !== $position->id) {
                $oldPosition = WarehousePosition::find($oldPositionId);
                if ($oldPosition && $oldPosition->warehouses()->count() === 0) {
                    $oldPosition->delete();
                }
            }

            DB::commit();

            Log::info('Warehouse item updated', [
                'warehouse_id' => $warehouse->id,
                'old_position_id' => $oldPositionId,
                'new_position_id' => $position->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Merce aggiornata con successo',
                'data' => $warehouse->load('position')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating warehouse item: ' . $e->getMessage(), [
                'warehouse_id' => $warehouse->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento della merce'
            ], 500);
        }
    }

    public function getPositionByProductionOrder(Request $request)
    {
        $productionOrder = $request->query('production_order');

        if (!$productionOrder) {
            return response()->json(['positions' => []]);
        }

        $positions = Warehouse::with('warehousePosition')
            ->where('production_order', $productionOrder)
            ->get()
            ->map(fn($w) => $w->warehousePosition?->warehouse_position)
            ->filter()
            ->unique()
            ->values();

        return response()->json(['positions' => $positions]);
    }

    public function destroy(Warehouse $warehouse)
    {
        try {
            DB::beginTransaction();

            $positionId = $warehouse->warehouse_position_id;
            $warehouseId = $warehouse->id;
            $warehouseData = $warehouse->toArray();

            $warehouse->delete();

            // Se la posizione è vuota, eliminala
            $position = WarehousePosition::find($positionId);
            if ($position && $position->warehouses()->count() === 0) {
                $position->delete();
            }

            DB::commit();

            Log::info('Warehouse item deleted', [
                'warehouse_id' => $warehouseId,
                'position_id' => $positionId,
                'data' => $warehouseData,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Merce eliminata dal magazzino con successo'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting warehouse item: ' . $e->getMessage(), [
                'warehouse_id' => $warehouse->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione della merce'
            ], 500);
        }
    }

    public function destroyPosition(WarehousePosition $position)
    {
        try {
            DB::beginTransaction();

            $positionId   = $position->id;
            $positionName = $position->warehouse_position;
            $count        = $position->warehouses()->count();

            // Elimina tutte le merci della posizione
            $position->warehouses()->delete();
            // Elimina la posizione stessa
            $position->delete();

            DB::commit();

            Log::info('Warehouse position deleted', [
                'position_id'   => $positionId,
                'position_name' => $positionName,
                'items_deleted' => $count,
                'user_id'       => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Posizione \"{$positionName}\" e {$count} element" . ($count === 1 ? 'o eliminato' : 'i eliminati') . ' con successo',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting warehouse position: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione della posizione',
            ], 500);
        }
    }

    // API: ricerca articolo da colata (A01_ART_SAD_LAM)
    public function lookupHeat(Request $request)
    {
        $cddet = $request->input('cddet');
        if (!$cddet) {
            return response()->json(['cdart' => null, 'cdfmt' => null]);
        }

        try {
            $result = DB::connection('sqlsrv_gestionale')
                ->selectOne('SELECT TOP 1 CDART, CDFMT FROM A01_ART_SAD_LAM WHERE CDDET = ?', [$cddet]);

            if ($result) {
                return response()->json([
                    'cdart' => $result->CDART,
                    'cdfmt' => $result->CDFMT,
                ]);
            }

            return response()->json(['cdart' => null, 'cdfmt' => null]);
        } catch (\Exception $e) {
            Log::error('Error looking up heat: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // API: aggiorna il nome di una posizione
    public function storePosition(Request $request)
    {
        $validated = $request->validate([
            'warehouse_position' => 'nullable|string|max:50',
            'quantity' => 'nullable|integer|min:0',
            'pending' => 'boolean',
        ]);

        $isPending = $validated['pending'] ?? false;
        $positionName = $validated['warehouse_position'] ?? '';

        if (!$isPending && empty($positionName)) {
            return response()->json([
                'success' => false,
                'message' => 'Il campo Posizione è obbligatorio quando non è in attesa.',
                'errors' => ['warehouse_position' => ['Il campo Posizione è obbligatorio.']]
            ], 422);
        }

        if (empty($positionName)) {
            $positionName = 'IN_ATTESA_' . strtoupper(uniqid());
        } elseif ($isPending && !str_starts_with(strtoupper($positionName), 'IN_ATTESA_')) {
            $positionName = 'IN_ATTESA_' . $positionName;
        }

        try {
            $position = WarehousePosition::firstOrCreate(
                ['warehouse_position' => $positionName],
                ['started' => false, 'quantity' => $validated['quantity'] ?? null]
            );

            if (!$position->wasRecentlyCreated && isset($validated['quantity'])) {
                $position->increment('quantity', $validated['quantity']);
                $position->refresh();
            }

            Log::info('Warehouse position created or found', [
                'position_id' => $position->id,
                'position' => $positionName,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Posizione creata con successo',
                'data' => $position
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating warehouse position: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione della posizione'
            ], 500);
        }
    }

    public function removeTerminated()
    {
        try {
            DB::beginTransaction();

            // Recupera tutti gli ordini di produzione presenti in magazzino
            $productionOrders = Warehouse::whereNotNull('production_order')
                ->where('production_order', '!=', '')
                ->pluck('production_order')
                ->unique()
                ->values()
                ->toArray();

            if (empty($productionOrders)) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Nessuna merce da rimuovere',
                    'deleted' => 0,
                ]);
            }

            // RECORD_ID è int: filtra solo i valori numerici prima di interrogare SQL Server
            $numericOrders = array_values(array_filter($productionOrders, fn($v) => ctype_digit((string) $v)));

            if (empty($numericOrders)) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Nessun ordine di produzione numerico da verificare',
                    'deleted' => 0,
                ]);
            }

            // Trova gli ordini di produzione con OPSTA = 'TE' in A01_ORD_PRO_ALL
            $terminated = DB::connection('sqlsrv_gestionale')
                ->table('A01_ORD_PRO_ALL')
                ->whereIn('RECORD_ID', $numericOrders)
                ->where('OPSTA', 'TE')
                ->pluck('RECORD_ID')
                ->map(fn($v) => (string) $v)
                ->toArray();

            if (empty($terminated)) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Nessuna merce con ordini terminati trovata',
                    'deleted' => 0,
                ]);
            }

            // Recupera le posizioni interessate prima di eliminare
            $affectedPositionIds = Warehouse::whereIn('production_order', $terminated)
                ->pluck('warehouse_position_id')
                ->unique()
                ->toArray();

            // Elimina le merci con ordini terminati
            $deleted = Warehouse::whereIn('production_order', $terminated)->count();
            Warehouse::whereIn('production_order', $terminated)->delete();

            // Elimina le posizioni rimaste vuote
            foreach ($affectedPositionIds as $positionId) {
                $position = WarehousePosition::find($positionId);
                if ($position && $position->warehouses()->count() === 0) {
                    $position->delete();
                }
            }

            DB::commit();

            Log::info('Removed terminated warehouse items', [
                'deleted' => $deleted,
                'production_orders' => $terminated,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Rimossi {$deleted} element" . ($deleted === 1 ? 'o' : 'i') . ' con ordini terminati',
                'deleted' => $deleted,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing terminated warehouse items: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la rimozione delle merci terminate'
            ], 500);
        }
    }

    public function updatePosition(Request $request, WarehousePosition $position)
    {
        $validated = $request->validate([
            'warehouse_position' => 'required|string|max:50',
            'started' => 'sometimes|boolean',
            'quantity' => 'sometimes|nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $oldPositionName = $position->warehouse_position;
            // Controlla se la posizione VECCHIA era "in attesa" (prima dell'update)
            // Usa una regex più precisa per identificare le posizioni IN_ATTESA
            $wasInAttesa = preg_match('/^IN_ATTESA_[A-Z0-9_]+$/i', $position->warehouse_position) === 1;

            // Controlla se la nuova posizione esiste già
            $existingPosition = WarehousePosition::where('warehouse_position', $validated['warehouse_position'])
                ->where('id', '!=', $position->id)
                ->first();

            if ($existingPosition) {
                // Se esiste già, sposta tutte le merci alla posizione esistente
                if ($wasInAttesa) {
                    // Se era in attesa, imposta pending=false quando viene spostato
                    $position->warehouses()->update([
                        'warehouse_position_id' => $existingPosition->id,
                        'pending' => false,
                        'pending_code' => null,
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    // Se non era in attesa, sposta solo la posizione senza modificare pending
                    $position->warehouses()->update([
                        'warehouse_position_id' => $existingPosition->id,
                        'updated_by' => Auth::id(),
                    ]);
                }

                // Elimina la vecchia posizione vuota
                $position->delete();

                if (array_key_exists('started', $validated)) {
                    $existingPosition->update(['started' => $validated['started']]);
                }
                if (array_key_exists('quantity', $validated)) {
                    $existingPosition->update(['quantity' => $validated['quantity']]);
                }

                $position = $existingPosition;
            } else {
                // Altrimenti aggiorna normalmente il nome della posizione
                $updateData = [
                    'warehouse_position' => $validated['warehouse_position'],
                ];
                if (array_key_exists('started', $validated)) {
                    $updateData['started'] = $validated['started'];
                }
                if (array_key_exists('quantity', $validated)) {
                    $updateData['quantity'] = $validated['quantity'];
                }
                $position->update($updateData);

                // Se la posizione era "in attesa", imposta pending=0 per tutte le merci
                if ($wasInAttesa) {
                    $position->warehouses()->update([
                        'pending' => false,
                        'pending_code' => null,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Warehouse position updated', [
                'position_id' => $position->id,
                'old_name' => $oldPositionName,
                'new_name' => $validated['warehouse_position'],
                'was_pending' => $wasInAttesa,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Posizione aggiornata con successo',
                'data' => $position
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating warehouse position: ' . $e->getMessage(), [
                'position_id' => $position->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento della posizione'
            ], 500);
        }
    }
}
