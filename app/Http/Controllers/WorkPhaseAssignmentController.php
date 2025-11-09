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
            $workPhases = DB::connection('sqlsrv_gestionale')
                ->table('A01_ORD_FAS')
                ->whereIn('RECORD_ID', $workPhaseIds)
                ->get()
                ->keyBy('RECORD_ID');
            
            // Aggiungi i dati delle work phases agli assignments
            $assignments->each(function ($assignment) use ($workPhases) {
                $assignment->work_phase = $workPhases->get($assignment->work_phase_id);
            });
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
