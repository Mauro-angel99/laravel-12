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

    // API: lista WorkPhases con ricerca e filtro date
    public function list(Request $request)
    {
        $search = $request->input('search', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        
        try {
            $query = 'SELECT TOP 200 RECORD_ID, FLASS, IDOPR, FLSEQ, FLLAV, FLDES, FLQTA, FLQTB, FLQTD, FLCON FROM dbo.A01_ORD_FAS';
            $params = [];
            $conditions = [];
            
            // Filtro per ricerca testuale
            if (!empty($search)) {
                $conditions[] = '(FLDES LIKE ? OR FLASS LIKE ?)';
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // Filtro per data da - usa CONVERT per gestire il formato stringa
            if (!empty($dateFrom)) {
                $conditions[] = 'CONVERT(DATETIME, FLCON, 120) >= CONVERT(DATETIME, ?, 120)';
                $params[] = $dateFrom . ' 00:00:00.000';
            }
            
            // Filtro per data a - usa CONVERT per gestire il formato stringa
            if (!empty($dateTo)) {
                $conditions[] = 'CONVERT(DATETIME, FLCON, 120) <= CONVERT(DATETIME, ?, 120)';
                $params[] = $dateTo . ' 23:59:59.999';
            }
            
            // Aggiungi condizioni alla query
            if (!empty($conditions)) {
                $query .= ' WHERE ' . implode(' AND ', $conditions);
            }
            
            // Aggiungi ordinamento decrescente per FLCON
            $query .= ' ORDER BY FLCON DESC';
            
            // Debug: log della query per troubleshooting
            \Log::info('WorkPhase Query: ' . $query);
            \Log::info('WorkPhase Params: ' . json_encode($params));
            
            $dati = DB::connection('sqlsrv_gestionale')
                ->select($query, $params);
                
        } catch (\Exception $e) {
            \Log::error('WorkPhase Error: ' . $e->getMessage());
            \Log::error('WorkPhase Query: ' . $query);
            \Log::error('WorkPhase Params: ' . json_encode($params));
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
