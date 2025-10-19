<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkPhaseController extends Controller
{
    // Vista principale
    public function index()
    {
        return view('workphases.index');
    }

    // API: lista WorkPhases con ricerca
    public function list(Request $request)
    {
        $search = $request->input('search', '');
        
        try {
            $query = 'SELECT TOP 20 RECORD_ID, FLASS, IDOPR, FLSEQ, FLLAV, FLDES, FLQTA, FLQTB, FLQTD, FLCON FROM dbo.A01_ORD_FAS';
            
            if (!empty($search)) {
                $query .= ' WHERE FLDES LIKE ? OR FLASS LIKE ?';
                $searchParam = '%' . $search . '%';
                $dati = DB::connection('sqlsrv_gestionale')
                    ->select($query, [$searchParam, $searchParam]);
            } else {
                $dati = DB::connection('sqlsrv_gestionale')
                    ->select($query);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($dati);
    }

    // API: conferma selezionati
    public function confirm(Request $request)
    {
        $selected = $request->input('selected', []);

        // logica per processare i WorkPhases selezionati
        // esempio: aggiornare lo stato nel DB

        return response()->json([
            'message' => count($selected) ? 'WorkPhases confermate con successo!' : 'Nessun WorkPhase selezionato.'
        ]);
    }
}
