<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('warehouse.index');
    }

    // API: lista warehouse con paginazione e filtri
    public function list(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $search = $request->input('search');
        $productCode = $request->input('product_code');
        $productionOrder = $request->input('production_order');
        $warehouseArea = $request->input('warehouse_area');

        $query = Warehouse::query();

        // Filtri
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('product_code', 'like', '%' . $search . '%')
                  ->orWhere('product_description', 'like', '%' . $search . '%')
                  ->orWhere('production_order', 'like', '%' . $search . '%');
            });
        }

        if (!empty($productCode)) {
            $query->where('product_code', 'like', '%' . $productCode . '%');
        }

        if (!empty($productionOrder)) {
            $query->where('production_order', 'like', '%' . $productionOrder . '%');
        }

        if (!empty($warehouseArea)) {
            $query->where('warehouse_area', 'like', '%' . $warehouseArea . '%');
        }

        $total = $query->count();
        $warehouses = $query->offset($offset)
            ->limit($perPage)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $warehouses,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code' => 'required|string|max:50',
            'product_description' => 'nullable|string|max:255',
            'production_order' => 'required|string|max:50',
            'warehouse_area' => 'required|string|max:50',
            'warehouse_position' => 'required|string|max:50',
            'quantity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Imposta valori di default per campi opzionali
        $validated['product_description'] = $validated['product_description'] ?? '';
        $validated['quantity'] = $validated['quantity'] ?? 0;

        $warehouse = Warehouse::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Elemento magazzino creato con successo',
            'data' => $warehouse
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        return response()->json($warehouse);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return response()->json($warehouse);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'product_code' => 'required|string|max:50',
            'product_description' => 'required|string|max:255',
            'production_order' => 'required|string|max:50',
            'warehouse_area' => 'required|string|max:50',
            'warehouse_position' => 'required|string|max:50',
            'quantity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Elemento magazzino aggiornato con successo',
            'data' => $warehouse
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Elemento magazzino eliminato con successo'
        ]);
    }
}
