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

    // API: lista WorkPhases con ricerca, filtro date e paginazione
    public function list(Request $request)
    {
        $search = $request->input('search', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        try {
            // Query per contare il totale dei record
            $countQuery = 'SELECT COUNT(*) as total FROM dbo.A01_ORD_FAS';
            $countParams = [];
            $countConditions = [];
            
            // Query principale per i dati
            $query = 'SELECT RECORD_ID, FLASS, IDOPR, FLSEQ, FLLAV, FLDES, FLQTA, FLQTB, FLQTD, FLCON FROM dbo.A01_ORD_FAS';
            $params = [];
            $conditions = [];
            
            // Filtro per ricerca testuale
            if (!empty($search)) {
                $condition = '(FLDES LIKE ? OR FLASS LIKE ?)';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $countParams[] = $searchParam;
                $countParams[] = $searchParam;
            }
            
            // Filtro per data da - usa CONVERT per gestire il formato stringa
            if (!empty($dateFrom)) {
                $condition = 'CONVERT(DATETIME, FLCON, 120) >= CONVERT(DATETIME, ?, 120)';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $params[] = $dateFrom . ' 00:00:00.000';
                $countParams[] = $dateFrom . ' 00:00:00.000';
            }
            
            // Filtro per data a - usa CONVERT per gestire il formato stringa
            if (!empty($dateTo)) {
                $condition = 'CONVERT(DATETIME, FLCON, 120) <= CONVERT(DATETIME, ?, 120)';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $params[] = $dateTo . ' 23:59:59.999';
                $countParams[] = $dateTo . ' 23:59:59.999';
            }
            
            // Aggiungi condizioni alle query
            $whereClause = '';
            if (!empty($conditions)) {
                $whereClause = ' WHERE ' . implode(' AND ', $conditions);
            }
            
            // Esegui query per contare il totale
            $countQuery .= $whereClause;
            $totalResult = DB::connection('sqlsrv_gestionale')
                ->select($countQuery, $countParams);
            $total = $totalResult[0]->total;
            
            // Aggiungi ordinamento e paginazione alla query principale
            $query .= $whereClause . ' ORDER BY FLCON ASC, RECORD_ID DESC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY';
            $params[] = $offset;
            $params[] = $perPage;
            
            // Debug: log della query per troubleshooting
            \Log::info('WorkPhase Query: ' . $query);
            \Log::info('WorkPhase Params: ' . json_encode($params));
            
            $dati = DB::connection('sqlsrv_gestionale')
                ->select($query, $params);
                
            // Calcola informazioni paginazione
            $lastPage = ceil($total / $perPage);
            $from = $offset + 1;
            $to = min($offset + $perPage, $total);
                
        } catch (\Exception $e) {
            \Log::error('WorkPhase Error: ' . $e->getMessage());
            \Log::error('WorkPhase Query: ' . $query);
            \Log::error('WorkPhase Params: ' . json_encode($params));
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'data' => $dati,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
                'has_more_pages' => $page < $lastPage
            ]
        ]);
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
