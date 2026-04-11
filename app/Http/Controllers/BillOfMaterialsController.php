<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}
