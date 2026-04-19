<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NonConformityController extends Controller
{
    /**
     * Ottiene le non conformità per un ordine di produzione
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'opart' => 'required|string|max:50',
        ]);

        try {
            $nonConformities = DB::connection('sqlsrv_gestionale')
                ->table('A01_NON_CON')
                ->select('NCRIL', 'NCCLA', 'NCART', 'NCDES')
                ->where('NCART', $request->opart)
                ->get();

            return response()->json($nonConformities);
        } catch (\Exception $e) {
            Log::error('Errore nel caricamento delle non conformità', [
                'error' => $e->getMessage(),
                'opart' => $request->opart
            ]);

            return response()->json([
                'message' => 'Errore nel caricamento delle non conformità',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
