<?php

namespace App\Http\Controllers;

use App\Models\WorkPhaseAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkPhaseAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Vista principale (Vue)
    public function index()
    {
        return view('assignedWorkPhases.index');
    }

    // API: lista fasi assegnate con filtro admin / utente
    public function list(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $query = WorkPhaseAssignment::query();

        // Se non admin, limita ai propri record
        $isAdmin = $user->roles()->where('name', 'admin')->exists();
        if (!$isAdmin) {
            $query->where('assigned_to', $user->id);
        }

        // Carica TUTTI gli assignments (senza paginazione) per poter filtrare
        // correttamente sui dati SQL Server prima di paginare
        $allAssignments = $query->with(['assignedUser', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $workPhaseIds = $allAssignments->pluck('work_phase_id')->unique()->toArray();

        // Determina se almeno un filtro è attivo
        $hasFilters = $request->filled('fllav')
            || $request->filled('dtras')
            || $request->filled('dtric')
            || $request->filled('dtnum')
            || $request->filled('idopr')
            || $request->filled('date_from')
            || $request->filled('date_to');

        if (!empty($workPhaseIds)) {
            // Carica i dati SQL Server per arricchire (sempre) e filtrare (solo se ci sono filtri attivi)
            $workPhaseQuery = DB::connection('sqlsrv_gestionale')
                ->table('A01_ORD_FAS')
                ->whereIn('RECORD_ID', $workPhaseIds);

            // Applica filtri su SQL Server solo se almeno uno è attivo
            if ($hasFilters) {
                if ($request->filled('fllav')) {
                    $workPhaseQuery->where('FLLAV', 'like', '%' . $request->input('fllav') . '%');
                }
                if ($request->filled('dtras')) {
                    $workPhaseQuery->where('DTRAS', 'like', '%' . $request->input('dtras') . '%');
                }
                if ($request->filled('dtric')) {
                    $workPhaseQuery->where('DTRIC', 'like', '%' . $request->input('dtric') . '%');
                }
                if ($request->filled('dtnum')) {
                    $workPhaseQuery->where('DTNUM', 'like', '%' . $request->input('dtnum') . '%');
                }
                if ($request->filled('idopr')) {
                    $workPhaseQuery->where('IDOPR', 'like', '%' . $request->input('idopr') . '%');
                }
                if ($request->filled('date_from')) {
                    $dateFrom = \DateTime::createFromFormat('d/m/Y', $request->input('date_from'));
                    if ($dateFrom) {
                        $workPhaseQuery->where('DTORD', '>=', $dateFrom->format('Y-m-d'));
                    }
                }
                if ($request->filled('date_to')) {
                    $dateTo = \DateTime::createFromFormat('d/m/Y', $request->input('date_to'));
                    if ($dateTo) {
                        $workPhaseQuery->where('DTORD', '<=', $dateTo->format('Y-m-d'));
                    }
                }
            }

            $workPhases = $workPhaseQuery->get()->keyBy('RECORD_ID');

            // Se ci sono filtri attivi ma nessun risultato su SQL Server → zero risultati
            if ($hasFilters && $workPhases->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => 0,
                        'last_page' => 1,
                        'from' => 0,
                        'to' => 0,
                    ]
                ]);
            }

            // Carica i dati OPART dalla vista A01_ORD_PRO_ALL usando IDOPR
            $opartData = collect();
            $idoprs = $workPhases->pluck('IDOPR')->filter()->unique()->toArray();
            if (!empty($idoprs)) {
                $opartData = DB::connection('sqlsrv_gestionale')
                    ->table('A01_ORD_PRO_ALL')
                    ->select('RECORD_ID', 'OPART')
                    ->whereIn('RECORD_ID', $idoprs)
                    ->get()
                    ->keyBy('RECORD_ID');
            }

            // Arricchisce gli assignments con i dati SQL Server.
            // Se ci sono filtri attivi, esclude gli assignments che non hanno un work phase corrispondente.
            // Se non ci sono filtri, mostra TUTTI gli assignments (anche quelli senza dati SQL Server).
            $filteredAssignments = $allAssignments->map(function ($assignment) use ($workPhases, $opartData, $hasFilters) {
                $workPhase = $workPhases->get($assignment->work_phase_id);
                if ($workPhase) {
                    if (isset($workPhase->IDOPR) && $workPhase->IDOPR) {
                        $opart = $opartData->get($workPhase->IDOPR);
                        if ($opart && isset($opart->OPART)) {
                            $workPhase->OPART = $opart->OPART;
                        }
                    }
                    $assignment->work_phase = $workPhase;
                } elseif ($hasFilters) {
                    // Con filtri attivi, escludi assignments senza corrispondenza
                    return null;
                }
                return $assignment;
            })->filter()->values();

            // Paginazione sul risultato già filtrato
            $total = $filteredAssignments->count();
            $paginatedAssignments = $filteredAssignments->slice($offset, $perPage)->values();

            return response()->json([
                'data' => $paginatedAssignments,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => max(1, ceil($total / $perPage)),
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $total),
                ]
            ]);
        }

        // Nessun assignment trovato
        return response()->json([
            'data' => [],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'last_page' => 1,
                'from' => 0,
                'to' => 0,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkPhaseAssignment $workPhaseAssignment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkPhaseAssignment $workPhaseAssignment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkPhaseAssignment $workPhaseAssignment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkPhaseAssignment $workPhaseAssignment)
    {
        //
    }

    /**
     * Update work parameters for an assignment
     */
    public function updateParameters(Request $request, $id)
    {
        $assignment = WorkPhaseAssignment::findOrFail($id);

        $validated = $request->validate([
            'job_code' => 'required|string',
            'art_code' => 'required|string',
            'parameter_values' => 'nullable|array',
        ]);

        // Trova o crea il record nella tabella job_parameter_values
        $jobParameterValue = \App\Models\JobParameterValue::updateOrCreate(
            [
                'job_code' => $validated['job_code'],
                'art_code' => $validated['art_code'],
            ],
            [
                'parameter_values' => $validated['parameter_values'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Parametri aggiornati con successo',
            'job_parameter_value' => $jobParameterValue
        ]);
    }

    /**
     * Get work parameters for a specific job and article
     */
    public function getParameters(Request $request)
    {
        $jobCode = $request->input('job_code');
        $artCode = $request->input('art_code');

        if (!$jobCode || !$artCode) {
            return response()->json(['parameter_values' => null]);
        }

        $jobParameterValue = \App\Models\JobParameterValue::where('job_code', $jobCode)
            ->where('art_code', $artCode)
            ->first();

        return response()->json([
            'parameter_values' => $jobParameterValue ? $jobParameterValue->parameter_values : null
        ]);
    }
}
