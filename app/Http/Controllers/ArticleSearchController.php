<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleSearchController extends Controller
{
    public function index()
    {
        return view('articleSearch.index');
    }

    public function list(Request $request)
    {
        $cdart = $request->input('cdart', '');
        $ardes = $request->input('ardes', '');
        $armat = $request->input('armat', '');
        $ardmz = $request->input('ardmz', '');
        $arprf = $request->input('arprf', '');
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        try {
            $countQuery = 'SELECT COUNT(*) as total FROM A01_ART_ICO';
            $countParams = [];
            $conditions = [];

            if (!empty($cdart)) {
                $conditions[] = 'CDART LIKE ?';
                $countParams[] = '%' . $cdart . '%';
            }
            if (!empty($ardes)) {
                $conditions[] = 'ARDES LIKE ?';
                $countParams[] = '%' . $ardes . '%';
            }
            if (!empty($armat)) {
                $conditions[] = 'ARMAT LIKE ?';
                $countParams[] = '%' . $armat . '%';
            }
            if (!empty($ardmz)) {
                $ardmzNormalized = str_replace(',', '.', $ardmz);
                $conditions[] = '(CAST(ARDMZ AS NVARCHAR) LIKE ? OR CAST(ARDMZ AS NVARCHAR) LIKE ?)';
                $countParams[] = '%' . str_replace('.', ',', $ardmzNormalized) . '%';
                $countParams[] = '%' . $ardmzNormalized . '%';
            }
            if (!empty($arprf)) {
                $conditions[] = 'ARPRF LIKE ?';
                $countParams[] = '%' . $arprf . '%';
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
                CDART,
                ARDES,
                ARMAT,
                ARDMZ,
                ARPRF
            FROM A01_ART_ICO';

            $params = array_values($countParams);

            $query .= $whereClause . ' ORDER BY CDART ASC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY';
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
