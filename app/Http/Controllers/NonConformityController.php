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
            'idopr' => 'required|string|max:50',
        ]);

        try {
            // Trova le non conformità tramite IDOPR -> OCART -> NCART
            $nonConformities = DB::connection('sqlsrv_gestionale')
                ->table('dbo.A01_NON_CON as nc')
                ->join('dbo.A01_ORD_COM_LAM as l', 'nc.NCART', '=', 'l.OCART')
                ->select('nc.NCRIL', 'nc.NCCLA', 'nc.NCART', 'nc.NCDES')
                ->where('l.IDORD', $request->idopr)
                ->get();

            return response()->json($nonConformities);
        } catch (\Exception $e) {
            Log::error('Errore nel caricamento delle non conformità', [
                'error' => $e->getMessage(),
                'idopr' => $request->idopr
            ]);

            return response()->json([
                'message' => 'Errore nel caricamento delle non conformità',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
