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
        if (!$user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }

        $total = $query->count();
        $assignments = $query->with(['assignedUser', 'assignedBy'])
            ->offset($offset)
            ->limit($perPage)
            ->orderBy('created_at', 'desc')
            ->get();

        // Carica manualmente i dati delle work phases dal database SQL Server
        $workPhaseIds = $assignments->pluck('work_phase_id')->unique()->toArray();
        
        if (!empty($workPhaseIds)) {
            $workPhaseQuery = DB::connection('sqlsrv_gestionale')
                ->table('A01_ORD_FAS')
                ->whereIn('RECORD_ID', $workPhaseIds);
            
            // Applica filtri se presenti
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
            
            $workPhases = $workPhaseQuery->get()->keyBy('RECORD_ID');
            
            // Aggiungi i dati delle work phases agli assignments e filtra
            $filteredAssignments = $assignments->filter(function ($assignment) use ($workPhases) {
                $workPhase = $workPhases->get($assignment->work_phase_id);
                if ($workPhase) {
                    $assignment->work_phase = $workPhase;
                    return true;
                }
                return false;
            })->values();
            
            return response()->json([
                'data' => $filteredAssignments,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $filteredAssignments->count(),
                    'last_page' => ceil($filteredAssignments->count() / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $filteredAssignments->count()),
                ]
            ]);
        }

        return response()->json([
            'data' => $assignments,
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
}
