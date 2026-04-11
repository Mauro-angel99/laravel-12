<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BomParameterValue;

class BillOfMaterialsController extends Controller
{
    public function index()
    {
        return view('billOfMaterials.index');
    }

    public function list(Request $request)
    {
        $dbart = $request->input('dbart', '');
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        try {
            $countQuery = 'SELECT COUNT(*) as total FROM dbo.A01_DIS_FAS_ALL';
            $countParams = [];
            $conditions = [];

            if (!empty($dbart)) {
                $conditions[] = 'DBART LIKE ?';
                $countParams[] = '%' . $dbart . '%';
            }

            $whereClause = '';
            if (!empty($conditions)) {
                $whereClause = ' WHERE ' . implode(' AND ', $conditions);
            }

            $countQuery .= $whereClause;
            $totalResult = DB::connection('sqlsrv_gestionale')
                ->select($countQuery, $countParams);
            $total = $totalResult[0]->total;

            $query = 'SELECT
                DLACT,
                DLSEQ,
                DBART,
                LVDES,
                DLNOT2,
                DLLAV,
                DLTAP,
                DLTMP,
                DLTUP
            FROM dbo.A01_DIS_FAS_ALL';

            $params = [];
            if (!empty($dbart)) {
                $params[] = '%' . $dbart . '%';
            }

            $query .= $whereClause . ' ORDER BY DBART ASC, DLSEQ ASC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY';
            $params[] = $offset;
            $params[] = $perPage;

            $dati = DB::connection('sqlsrv_gestionale')
                ->select($query, $params);

            $lastPage = max(1, ceil($total / $perPage));
            $from = $total > 0 ? $offset + 1 : 0;
            $to = min($offset + $perPage, $total);
        } catch (\Exception $e) {
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
            ]
        ]);
    }

    public function updateParameters(Request $request)
    {
        $validated = $request->validate([
            'dllav' => 'required|string',
            'dbart' => 'required|string',
            'parameter_values' => 'nullable|array',
        ]);

        $bomParameterValue = BomParameterValue::updateOrCreate(
            [
                'dllav' => $validated['dllav'],
                'dbart' => $validated['dbart'],
            ],
            [
                'parameter_values' => $validated['parameter_values'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Parametri aggiornati con successo',
            'bom_parameter_value' => $bomParameterValue
        ]);
    }

    public function getParameters(Request $request)
    {
        $dllav = $request->input('dllav');
        $dbart = $request->input('dbart');

        if (!$dllav || !$dbart) {
            return response()->json(['parameter_values' => null]);
        }

        $bomParameterValue = BomParameterValue::where('dllav', $dllav)
            ->where('dbart', $dbart)
            ->first();

        return response()->json([
            'parameter_values' => $bomParameterValue ? $bomParameterValue->parameter_values : null
        ]);
    }
}
