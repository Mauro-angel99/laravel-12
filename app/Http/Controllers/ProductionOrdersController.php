<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;

class ProductionOrdersController extends Controller
{
    public function index()
    {
        return view('production-orders.index');
    }

    public function list(Request $request)
    {
        $opras     = $request->input('opras', '');
        $opdnr     = $request->input('opdnr', '');
        $drconFrom = $request->input('drcon_from', '');
        $drconTo   = $request->input('drcon_to', '');
        $drcorFrom = $request->input('drcor_from', '');
        $drcorTo   = $request->input('drcor_to', '');
        $opart     = $request->input('opart', '');
        $page      = max(1, (int) $request->input('page', 1));
        $perPage   = 20;
        $offset    = ($page - 1) * $perPage;

        $conditions = [];
        $params     = [];

        if (!empty($opras)) {
            $conditions[] = 'OPRAS LIKE ?';
            $params[]     = '%' . $opras . '%';
        }
        if (!empty($opdnr)) {
            $conditions[] = 'CAST(OPDNR AS NVARCHAR) = ?';
            $params[]     = $opdnr;
        }
        if (!empty($drconFrom)) {
            $conditions[] = 'CAST(DRCON AS DATE) >= ?';
            $params[]     = $drconFrom;
        }
        if (!empty($drconTo)) {
            $conditions[] = 'CAST(DRCON AS DATE) <= ?';
            $params[]     = $drconTo;
        }
        if (!empty($drcorFrom)) {
            $conditions[] = 'CAST(DRCOR AS DATE) >= ?';
            $params[]     = $drcorFrom;
        }
        if (!empty($drcorTo)) {
            $conditions[] = 'CAST(DRCOR AS DATE) <= ?';
            $params[]     = $drcorTo;
        }
        if (!empty($opart)) {
            $conditions[] = 'OPART = ?';
            $params[]     = $opart;
        }

        $whereClause = !empty($conditions)
            ? ' WHERE ' . implode(' AND ', $conditions)
            : '';

        try {
            $totalResult = DB::connection('sqlsrv_gestionale')
                ->select('SELECT COUNT(*) as total FROM A01_SRC_PRO_FAS' . $whereClause, $params);
            $total = $totalResult[0]->total;

            $dataParams   = array_merge($params, [$offset, $perPage]);
            $dati = DB::connection('sqlsrv_gestionale')->select(
                'SELECT
                    OPASS,
                    RECORD_ID,
                    OPART,
                    OPDNR,
                    DTRIC,
                    OPRAS,
                    OPCMM,
                    OPUMP,
                    OPQTA,
                    OPQTP,
                    OPQTD,
                    FASI,
                    ARMAT,
                    DRCON,
                    DRCOR
                FROM A01_SRC_PRO_FAS'
                    . $whereClause
                    . ' ORDER BY RECORD_ID DESC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY',
                $dataParams
            );

            $lastPage = max(1, (int) ceil($total / $perPage));
            $from     = $total > 0 ? $offset + 1 : 0;
            $to       = min($offset + $perPage, $total);

            // Recupera posizioni magazzino (DB locale) per i RECORD_ID della pagina corrente
            $recordIds = array_map(fn($row) => (string) $row->RECORD_ID, $dati);
            $positionsGrouped = [];
            if (!empty($recordIds)) {
                $wItems = Warehouse::with('warehousePosition:id,warehouse_position')
                    ->whereIn('production_order', $recordIds)
                    ->get();
                foreach ($wItems as $w) {
                    $key = (string) $w->production_order;
                    if ($w->warehousePosition) {
                        $pos = $w->warehousePosition->warehouse_position;
                        if (!in_array($pos, $positionsGrouped[$key] ?? [])) {
                            $positionsGrouped[$key][] = $pos;
                        }
                    }
                }
            }

            $dati = array_map(function ($row) use ($positionsGrouped) {
                $arr = (array) $row;
                $arr['positions'] = $positionsGrouped[(string) $row->RECORD_ID] ?? [];
                return $arr;
            }, $dati);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'data' => $dati,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => $lastPage,
                'from'         => $from,
                'to'           => $to,
            ],
        ]);
    }
}
