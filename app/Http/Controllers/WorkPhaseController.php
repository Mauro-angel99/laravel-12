<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WorkPhaseAssignment;

class WorkPhaseController extends Controller
{
    // Vista principale
    public function index()
    {
        return view('workphases.index');
    }

    public function list(Request $request)
    {
        $search = $request->input('search', '');
        $fllav = $request->input('fllav', '');
        $dtras = $request->input('dtras', '');
        $dtric = $request->input('dtric', '');
        $dtnum = $request->input('dtnum', '');
        $idopr = $request->input('idopr', '');
        $onlyWorked = $request->input('only_worked', '');
        $onlyAvailable = $request->input('only_available', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        try {
            // Query per contare il totale dei record
            $countQuery = 'SELECT COUNT(*) as total FROM dbo.A01_ORD_FAS f
            LEFT JOIN dbo.A01_ORD_COM_LAM l ON f.IDOPR = l.IDORD
            LEFT JOIN dbo.A01_DOC_VER_ALL d ON f.FLASS = d.DROPR';
            $countParams = [];
            $countConditions = [];
            
            // Query principale per i dati (senza ORDER BY)
            $query = 'SELECT 
                f.RECORD_ID,
                f.FLASS,
                f.IDOPR,
                f.FLSEQ,
                f.FLLAV,
                f.FLDES,
                f.FLQTA,
                f.FLQTB,
                f.FLQTD,
                f.FLCON,
                l.RECORD_ID AS RECORD_ID_L,
                l.IDORD AS L_IDORD,
                d.RECORD_ID AS RECORD_ID_D,
                d.DROPR AS D_DROPR,
                d.DTRAS,
                d.DTRIC,
                d.DTNUM
            FROM dbo.A01_ORD_FAS f
            LEFT JOIN dbo.A01_ORD_COM_LAM l ON f.IDOPR = l.IDORD
            LEFT JOIN dbo.A01_DOC_VER_ALL d ON f.FLASS = d.DROPR';
            $params = [];
            $conditions = [];
            
            // Filtro per ricerca testuale
            if (!empty($search)) {
                $condition = '(f.FLDES LIKE ? OR f.FLASS LIKE ?)';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $countParams[] = $searchParam;
                $countParams[] = $searchParam;
            }
            
            // Filtro per FLLAV (Codice Lav)
            if (!empty($fllav)) {
                $condition = 'f.FLLAV LIKE ?';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $searchParam = '%' . $fllav . '%';
                $params[] = $searchParam;
                $countParams[] = $searchParam;
            }
            
            // Filtro per DTRAS (Rag. Soc.)
            if (!empty($dtras)) {
                $condition = 'd.DTRAS LIKE ?';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $searchParam = '%' . $dtras . '%';
                $params[] = $searchParam;
                $countParams[] = $searchParam;
            }
            
            // Filtro per DTRIC (N. Ord. Cli.)
            if (!empty($dtric)) {
                $condition = 'd.DTRIC LIKE ?';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $searchParam = '%' . $dtric . '%';
                $params[] = $searchParam;
                $countParams[] = $searchParam;
            }
            
            // Filtro per DTNUM (N. Ns. Ord.)
            if (!empty($dtnum)) {
                $condition = 'd.DTNUM LIKE ?';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $searchParam = '%' . $dtnum . '%';
                $params[] = $searchParam;
                $countParams[] = $searchParam;
            }
            
            // Filtro per IDOPR (Ord. Prod.)
            if (!empty($idopr)) {
                $condition = 'f.IDOPR LIKE ?';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $searchParam = '%' . $idopr . '%';
                $params[] = $searchParam;
                $countParams[] = $searchParam;
            }
            
            // Filtro per solo lavorati (FLQTB = 0)
            if (!empty($onlyWorked)) {
                $condition = 'f.FLQTB = 0';
                $conditions[] = $condition;
                $countConditions[] = $condition;
            }
            
            // Filtro per solo disponibili (FLQTD > 0 AND FLQTB = 0)
            if (!empty($onlyAvailable)) {
                $condition = '(f.FLQTD > 0 AND f.FLQTB = 0)';
                $conditions[] = $condition;
                $countConditions[] = $condition;
            }
            
            // Filtro per date
            if (!empty($dateFrom)) {
                $condition = 'CONVERT(DATETIME, f.FLCON, 120) >= CONVERT(DATETIME, ?, 120)';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $params[] = $dateFrom . ' 00:00:00.000';
                $countParams[] = $dateFrom . ' 00:00:00.000';
            }
            if (!empty($dateTo)) {
                $condition = 'CONVERT(DATETIME, f.FLCON, 120) <= CONVERT(DATETIME, ?, 120)';
                $conditions[] = $condition;
                $countConditions[] = $condition;
                $params[] = $dateTo . ' 23:59:59.999';
                $countParams[] = $dateTo . ' 23:59:59.999';
            }
            
            // Costruisci WHERE
            $whereClause = '';
            if (!empty($conditions)) {
                $whereClause = ' WHERE ' . implode(' AND ', $conditions);
            }
            
            // Conta totale
            $countQuery .= $whereClause;
            //\Log::info('Count Query: ' . $countQuery);
            //\Log::info('Count Params: ' . json_encode($countParams));
            
            $totalResult = DB::connection('sqlsrv_gestionale')
                ->select($countQuery, $countParams);
            $total = $totalResult[0]->total;
            
            // Aggiungi WHERE, ORDER BY e OFFSET/FETCH
            $query .= $whereClause . ' ORDER BY f.FLCON ASC, f.RECORD_ID DESC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY';
            $params[] = $offset;
            $params[] = $perPage;
            
            //\Log::info('Main Query: ' . $query);
            //\Log::info('Main Params: ' . json_encode($params));
            
            $dati = DB::connection('sqlsrv_gestionale')
                ->select($query, $params);
            
            // Recupera gli ID delle fasi già assegnate dal database Laravel
            $assignedPhaseIds = WorkPhaseAssignment::pluck('work_phase_id')->toArray();
            
            // Aggiungi il flag is_assigned a ogni record
            foreach ($dati as $record) {
                $record->is_assigned = in_array($record->RECORD_ID, $assignedPhaseIds) ? 1 : 0;
            }
            
            $lastPage = ceil($total / $perPage);
            $from = $offset + 1;
            $to = min($offset + $perPage, $total);
            
        } catch (\Exception $e) {
            //\Log::error('WorkPhase Error: ' . $e->getMessage());
            //\Log::error('WorkPhase Query: ' . $query);
            //\Log::error('WorkPhase Params: ' . json_encode($params));
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

    public function assign(Request $request)
    {
        $request->validate([
            'selected' => 'required|array|min:1',
            'selected.*' => 'integer',
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $created = [];
        foreach ($request->selected as $phaseId) {
            $created[] = WorkPhaseAssignment::create([
                'work_phase_id' => $phaseId,
                'assigned_to' => $request->assigned_to,
                'assigned_by' => $request->user()->id,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);
        }

        return response()->json([
            'message' => count($created) . ' fasi assegnate con successo!',
            'data' => $created
        ]);
    }
}
