<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehousePosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SheetsController extends Controller
{
    public function index()
    {
        return view('sheets.index');
    }

    // API: lista merci (lamiere) con paginazione e filtro per codice merce
    public function list(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $search = $request->input('search');
        $pending = $request->input('pending');

        $query = Warehouse::with('warehousePosition:id,warehouse_position')
            ->select('warehouses.*');

        if ($pending !== null) {
            $query->where('pending', (bool) $pending);
        }

        if (!empty($search)) {
            $query->where('product_code', 'like', '%' . $search . '%');
        }

        $total = $query->count();

        $items = $query
            ->orderBy('product_code')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(function ($item) {
                return [
                    'id'               => $item->id,
                    'heat'             => $item->heat,
                    'product_code'     => $item->product_code,
                    'position'         => $item->warehousePosition?->warehouse_position,
                    'production_order' => $item->production_order,
                    'format'           => $item->format,
                    'started'          => (bool) $item->started,
                    'pending'          => (bool) $item->pending,
                    'pending_code'     => $item->pending_code,
                ];
            });

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
                'from'         => $total > 0 ? $offset + 1 : 0,
                'to'           => min($offset + $perPage, $total),
            ],
        ]);
    }

    // API: aggiungi merce (lamiera)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pending'          => 'boolean',
            'pending_code'     => 'nullable|string|max:50',
            'heat'             => 'nullable|string|max:100',
            'product_code'     => 'nullable|string|max:50',
            'position'         => 'nullable|string|max:50',
            'production_order' => 'nullable|string|max:50',
            'format'           => 'nullable|string|max:100',
        ]);

        $pending = $validated['pending'] ?? false;

        if (!$pending && empty($validated['position'])) {
            return response()->json([
                'success' => false,
                'message' => 'Il campo Posizione è obbligatorio quando non è in attesa.',
            ], 422);
        }

        if ($pending && empty($validated['position'])) {
            $pendingCode = $validated['pending_code'] ?? uniqid('TEMP_', true);
            $validated['position'] = 'IN_ATTESA_' . $pendingCode;
        }

        $position = WarehousePosition::firstOrCreate(
            ['warehouse_position' => $validated['position']]
        );

        $item = Warehouse::create([
            'warehouse_position_id' => $position->id,
            'heat'                  => $validated['heat'] ?? null,
            'product_code'          => $validated['product_code'] ?? null,
            'production_order'      => $validated['production_order'] ?? null,
            'format'                => $validated['format'] ?? null,
            'pending'               => $pending,
            'pending_code'          => $validated['pending_code'] ?? null,
            'created_by'            => Auth::id(),
            'received_at'           => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merce aggiunta con successo.',
            'data'    => [
                'id'               => $item->id,
                'heat'             => $item->heat,
                'product_code'     => $item->product_code,
                'position'         => $position->warehouse_position,
                'production_order' => $item->production_order,
                'format'           => $item->format,
            ],
        ], 201);
    }

    // API: aggiorna merce
    public function update(Request $request, $id)
    {
        $item = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'pending'          => 'boolean',
            'pending_code'     => 'nullable|string|max:50',
            'heat'             => 'nullable|string|max:100',
            'product_code'     => 'nullable|string|max:50',
            'position'         => 'required|string|max:50',
            'production_order' => 'nullable|string|max:50',
            'format'           => 'nullable|string|max:100',
            'started'          => 'boolean',
        ]);

        // Se l'utente ha assegnato una posizione reale, la merce non è più in attesa
        $isRealPosition = !empty($validated['position']) && !str_starts_with($validated['position'], 'IN_ATTESA_');
        $pending = $isRealPosition ? false : ($validated['pending'] ?? false);

        try {
            DB::beginTransaction();

            $oldPositionId = $item->warehouse_position_id;

            $position = WarehousePosition::firstOrCreate(
                ['warehouse_position' => $validated['position']]
            );

            $item->update([
                'warehouse_position_id' => $position->id,
                'heat'                  => $validated['heat'] ?? null,
                'product_code'          => $validated['product_code'] ?? null,
                'production_order'      => $validated['production_order'] ?? null,
                'format'                => $validated['format'] ?? null,
                'started'               => $validated['started'] ?? false,
                'pending'               => $pending,
                'pending_code'          => $pending ? ($validated['pending_code'] ?? null) : null,
                'updated_by'            => Auth::id(),
            ]);

            // Se la posizione è cambiata, elimina la vecchia se è rimasta vuota
            if ($oldPositionId !== $position->id) {
                $oldPosition = WarehousePosition::find($oldPositionId);
                if ($oldPosition && $oldPosition->warehouses()->count() === 0) {
                    $oldPosition->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merce aggiornata con successo.',
                'data'    => [
                    'id'           => $item->id,
                    'product_code' => $item->product_code,
                    'position'     => $position->warehouse_position,
                    'format'       => $item->format,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento della merce.',
            ], 500);
        }
    }

    // API: elimina merce
    public function destroy($id)
    {
        $item = Warehouse::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Merce eliminata con successo.',
        ]);
    }
}
