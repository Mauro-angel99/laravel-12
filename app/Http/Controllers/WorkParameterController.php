<?php

namespace App\Http\Controllers;

use App\Models\WorkParameter;
use App\Http\Requests\StoreWorkParameterRequest;
use App\Http\Requests\UpdateWorkParameterRequest;
use App\Http\Resources\WorkParameterResource;
use App\Services\AuditLogger;
use App\Services\WorkPhaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkParameterController extends Controller
{
    public function __construct(
        private AuditLogger $auditLogger,
        private WorkPhaseService $workPhaseService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $parameters = $this->workPhaseService->getWorkParameters();
            return response()->json($parameters);
        } catch (\Exception $e) {
            Log::error('Work parameters fetch error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Errore nel caricamento dei parametri'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkParameterRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            $parameter = WorkParameter::create($validated);
            
            $this->auditLogger->logWorkParameter('created', $parameter->id, $validated);
            $this->workPhaseService->clearCache();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Parametro creato con successo',
                'data' => new WorkParameterResource($parameter)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->auditLogger->logCritical('Work parameter creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);
            
            return response()->json([
                'error' => 'Errore durante la creazione del parametro'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkParameter $workParameter): JsonResponse
    {
        return response()->json(new WorkParameterResource($workParameter));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkParameterRequest $request, WorkParameter $workParameter): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            $workParameter->update($validated);
            
            $this->auditLogger->logWorkParameter('updated', $workParameter->id, $validated);
            $this->workPhaseService->clearParameterCache($workParameter->id);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Parametro aggiornato con successo',
                'data' => new WorkParameterResource($workParameter)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->auditLogger->logCritical('Work parameter update failed', [
                'error' => $e->getMessage(),
                'parameter_id' => $workParameter->id,
                'data' => $request->validated()
            ]);
            
            return response()->json([
                'error' => 'Errore durante l\'aggiornamento del parametro'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkParameter $workParameter): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $parameterId = $workParameter->id;
            $parameterName = $workParameter->name;
            
            $workParameter->delete();
            
            $this->auditLogger->logWorkParameter('deleted', $parameterId, [
                'name' => $parameterName
            ]);
            $this->workPhaseService->clearCache();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Parametro eliminato con successo'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->auditLogger->logCritical('Work parameter deletion failed', [
                'error' => $e->getMessage(),
                'parameter_id' => $workParameter->id
            ]);
            
            return response()->json([
                'error' => 'Errore durante l\'eliminazione del parametro'
            ], 500);
        }
    }
}
