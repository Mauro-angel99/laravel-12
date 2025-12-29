<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehousePosition;
use Illuminate\Http\Request;

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
            'notes' => 'nullable|string',
            'pending' => 'boolean',
            'pending_code' => 'nullable|string|max:50',
        ]);

        // Se pending è false, warehouse_position è obbligatorio
        if (!($validated['pending'] ?? false) && empty($validated['warehouse_position'])) {
            return response()->json([
                'success' => false,
                'message' => 'Il campo Posizione è obbligatorio quando non è in attesa.',
                'errors' => ['warehouse_position' => ['Il campo Posizione è obbligatorio.']]
            ], 422);
        }

        // Se pending è true e non c'è posizione, usa pending_code come posizione
        if (($validated['pending'] ?? false) && empty($validated['warehouse_position'])) {
            $validated['warehouse_position'] = 'IN_ATTESA_' . ($validated['pending_code'] ?? 'TEMP');
        }

        // Trova o crea la posizione
        $position = WarehousePosition::firstOrCreate(
            [
                'warehouse_position' => $validated['warehouse_position'],
            ]
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
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merce aggiunta al magazzino con successo',
            'data' => $warehouse->load('position')
        ], 201);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'warehouse_position' => 'required|string|max:50',
            'product_code' => 'nullable|string|max:50',
            'product_description' => 'nullable|string|max:255',
            'production_order' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'pending' => 'boolean',
            'pending_code' => 'nullable|string|max:50',
        ]);

        $oldPositionId = $warehouse->warehouse_position_id;

        // Trova o crea la nuova posizione
        $position = WarehousePosition::firstOrCreate(
            [
                'warehouse_position' => $validated['warehouse_position'],
            ]
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
        ]);

        // Se la posizione è cambiata, elimina la vecchia se è vuota
        if ($oldPositionId !== $position->id) {
            $oldPosition = WarehousePosition::find($oldPositionId);
            if ($oldPosition && $oldPosition->warehouses()->count() === 0) {
                $oldPosition->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Merce aggiornata con successo',
            'data' => $warehouse->load('position')
        ]);
    }

    public function destroy(Warehouse $warehouse)
    {
        $positionId = $warehouse->warehouse_position_id;
        $warehouse->delete();

        // Se la posizione è vuota, eliminala
        $position = WarehousePosition::find($positionId);
        if ($position && $position->warehouses()->count() === 0) {
            $position->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Merce eliminata dal magazzino con successo'
        ]);
    }

    // API: aggiorna il nome di una posizione
    public function updatePosition(Request $request, WarehousePosition $position)
    {
        $validated = $request->validate([
            'warehouse_position' => 'required|string|max:50',
        ]);

        // Controlla se la posizione VECCHIA era "in attesa" (prima dell'update)
        $wasInAttesa = strpos($position->warehouse_position, 'IN_ATTESA_') === 0;

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
                ]);
            } else {
                // Se non era in attesa, sposta solo la posizione senza modificare pending
                $position->warehouses()->update([
                    'warehouse_position_id' => $existingPosition->id,
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
                $position->warehouses()->update(['pending' => false, 'pending_code' => null]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Posizione aggiornata con successo',
            'data' => $position
        ]);
    }
}
