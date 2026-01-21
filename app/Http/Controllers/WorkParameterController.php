<?php

namespace App\Http\Controllers;

use App\Models\WorkParameter;
use Illuminate\Http\Request;

class WorkParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parameters = WorkParameter::orderBy('name')->get();
        return response()->json($parameters);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:work_parameters,name',
        ]);

        $parameter = WorkParameter::create($validated);
        
        return response()->json([
            'message' => 'Parametro creato con successo',
            'parameter' => $parameter
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkParameter $workParameter)
    {
        return response()->json($workParameter);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkParameter $workParameter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:work_parameters,name,' . $workParameter->id,
        ]);

        $workParameter->update($validated);
        
        return response()->json([
            'message' => 'Parametro aggiornato con successo',
            'parameter' => $workParameter
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkParameter $workParameter)
    {
        $workParameter->delete();
        
        return response()->json([
            'message' => 'Parametro eliminato con successo'
        ]);
    }
}
