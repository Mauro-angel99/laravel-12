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

        $query = Warehouse::with('warehousePosition:id,warehouse_position')
            ->select('warehouses.*');

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
                    'id'           => $item->id,
                    'product_code' => $item->product_code,
                    'position'     => $item->warehousePosition?->warehouse_position,
                    'format'       => $item->format,
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
            'product_code' => 'nullable|string|max:50',
            'position'     => 'required|string|max:50',
            'format'       => 'nullable|string|max:100',
        ]);

        $position = WarehousePosition::firstOrCreate(
            ['warehouse_position' => $validated['position']]
        );

        $item = Warehouse::create([
            'warehouse_position_id' => $position->id,
            'product_code'          => $validated['product_code'] ?? null,
            'format'                => $validated['format'] ?? null,
            'created_by'            => Auth::id(),
            'received_at'           => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merce aggiunta con successo.',
            'data'    => [
                'id'           => $item->id,
                'product_code' => $item->product_code,
                'position'     => $position->warehouse_position,
                'format'       => $item->format,
            ],
        ], 201);
    }
}
