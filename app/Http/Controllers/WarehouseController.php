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
            $query->whereHas('warehouses', function($wq) use ($pendingBool) {
                $wq->where('pending', $pendingBool);
            });
        }

        // Filtri di ricerca
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('warehouse_position', 'like', '%' . $search . '%')
                  ->orWhereHas('warehouses', function($wq) use ($search) {
                      $wq->where('product_code', 'like', '%' . $search . '%')
                         ->orWhere('production_order', 'like', '%' . $search . '%');
                  });
            });
        }

        $total = $query->count();
        $positions = $query->offset($offset)
            ->limit($perPage)
            ->orderBy('warehouse_position')
            ->get();

        // Se il filtro pending è attivo, aggiungi il pending_code alla risposta
        if ($pending !== null && (bool) $pending) {
            $positions->each(function($position) {
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
        $position = WarehousePosition::with('warehouses.position')->findOrFail($positionId);
        
        return response()->json([
            'position' => $position,
            'products' => $position->warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_position' => 'nullable|string|max:50',
            'product_code' => 'nullable|string|max:50',
            'product_description' => 'nullable|string|max:255',
            'production_order' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
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
                'product_code' => $validated['product_code'],
                'production_order' => $validated['production_order'],
                'product_description' => $validated['product_description'],
                'notes' => $validated['notes'] ?? null,
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
            'product_code' => 'nullable|string|max:50',
            'product_description' => 'nullable|string|max:255',
            'production_order' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'pending' => 'boolean',
            'pending_code' => 'nullable|string|max:50',
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
                'product_code' => $validated['product_code'],
                'production_order' => $validated['production_order'],
                'product_description' => $validated['product_description'],
                'notes' => $validated['notes'] ?? null,
                'pending' => $validated['pending'] ?? false,
                'pending_code' => $validated['pending_code'] ?? null,
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

    // API: aggiorna il nome di una posizione
    public function updatePosition(Request $request, WarehousePosition $position)
    {
        $validated = $request->validate([
            'warehouse_position' => 'required|string|max:50',
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
                
                $position = $existingPosition;
            } else {
                // Altrimenti aggiorna normalmente il nome della posizione
                $position->update([
                    'warehouse_position' => $validated['warehouse_position'],
                ]);

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
