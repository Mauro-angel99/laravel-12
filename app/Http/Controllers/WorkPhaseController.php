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

    // API: lista WorkPhases
    public function list()
    {
        try {
            $dati = DB::connection('sqlsrv_gestionale')
                ->select('SELECT TOP 50 RECORD_ID, FLASS, FLDES FROM dbo.A01_ORD_FAS');
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
