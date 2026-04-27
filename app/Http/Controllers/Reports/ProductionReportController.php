<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{ProductionOrder, Branch, ChartOfAccounts};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionReportController extends Controller
{
    public function index(Request $request)
    {
        $tab  = $request->get('tab', 'OR');
        $from = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $to   = $request->get('to_date',   Carbon::now()->toDateString());

        $branches = Branch::where('is_active', 1)->get();
        $vendors  = ChartOfAccounts::vendors()->orderBy('name')->get();

        $data = match($tab) {
            'OR'  => $this->orderRegister($from, $to, $request),
            'RM'  => $this->rawMaterialUsage($from, $to, $request),
            'FG'  => $this->finishedGoods($from, $to, $request),
            'OS'  => $this->outsourceReport($from, $to, $request),
            'SUM' => $this->summary($from, $to, $request),
            default => collect(),
        };

        return view('reports.production.index', compact('tab','from','to','data','branches','vendors'));
    }

    // ── TAB 1: Production Order Register ─────────────────────────────────
    private function orderRegister($from, $to, $request)
    {
        return ProductionOrder::with('branch')
            ->whereBetween('order_date', [$from, $to])
            ->when($request->branch_id,  fn($q) => $q->where('branch_id',  $request->branch_id))
            ->when($request->status,     fn($q) => $q->where('status',     $request->status))
            ->when($request->type,       fn($q) => $q->where('type',       $request->type))
            ->orderBy('order_date')
            ->get()
            ->map(fn($o) => (object)[
                'order_id'       => $o->id,
                'production_no'  => $o->production_no,
                'order_date'     => $o->order_date,
                'expected_date'  => $o->expected_date,
                'branch'         => optional($o->branch)->name ?? '—',
                'type'           => $o->type,
                'status'         => $o->status,
                'total_raw_cost' => (float)$o->total_raw_cost,
                'outsource_amount'=> (float)$o->outsource_amount,
                'total_cost'     => (float)$o->total_raw_cost + (float)$o->outsource_amount,
                'remarks'        => $o->remarks,
            ]);
    }

    // ── TAB 2: Raw Material Usage ─────────────────────────────────────────
    private function rawMaterialUsage($from, $to, $request)
    {
        return DB::table('production_raw_materials as prm')
            ->join('production_orders as po',        'prm.production_order_id', '=', 'po.id')
            ->join('product_variations as pv',        'prm.variation_id', '=', 'pv.id')
            ->join('products as p',                   'pv.product_id', '=', 'p.id')
            ->join('measurement_units as mu',         'prm.unit_id', '=', 'mu.id')
            ->select(
                'po.production_no',
                'po.order_date',
                'po.status',
                'p.name as product_name',
                'pv.sku',
                'pv.variation_name',
                'mu.shortcode as unit',
                'prm.quantity_required',
                'prm.quantity_issued',
                DB::raw('prm.quantity_required - prm.quantity_issued as quantity_pending'),
                'prm.unit_cost',
                DB::raw('prm.quantity_issued * prm.unit_cost as total_cost')
            )
            ->whereBetween('po.order_date', [$from, $to])
            ->when($request->branch_id, fn($q) => $q->where('po.branch_id', $request->branch_id))
            ->whereNull('po.deleted_at')
            ->orderBy('po.order_date')
            ->orderBy('po.production_no')
            ->get();
    }

    // ── TAB 3: Finished Goods Received ────────────────────────────────────
    private function finishedGoods($from, $to, $request)
    {
        return DB::table('production_receipt_items as pri')
            ->join('production_receipts as pr',   'pri.receipt_id',    '=', 'pr.id')
            ->join('production_orders as po',     'pr.production_order_id', '=', 'po.id')
            ->join('product_variations as pv',    'pri.variation_id',  '=', 'pv.id')
            ->join('products as p',               'pv.product_id',     '=', 'p.id')
            ->join('measurement_units as mu',     'pri.unit_id',       '=', 'mu.id')
            ->select(
                'po.production_no',
                'pr.receipt_no',
                'pr.receipt_date',
                'p.name as product_name',
                'pv.sku',
                'pv.variation_name',
                'mu.shortcode as unit',
                'pri.quantity_received',
                'pri.quantity_defective',
                DB::raw('pri.quantity_received - pri.quantity_defective as good_qty'),
                'pri.unit_cost',
                DB::raw('(pri.quantity_received - pri.quantity_defective) * pri.unit_cost as total_value')
            )
            ->whereBetween('pr.receipt_date', [$from, $to])
            ->when($request->branch_id, fn($q) => $q->where('po.branch_id', $request->branch_id))
            ->whereNull('po.deleted_at')
            ->orderBy('pr.receipt_date')
            ->get();
    }

    // ── TAB 4: Outsource Report ───────────────────────────────────────────
    private function outsourceReport($from, $to, $request)
    {
        return ProductionOrder::with('branch','vendor')
            ->where('type', 'outsource')
            ->whereBetween('order_date', [$from, $to])
            ->when($request->branch_id,    fn($q) => $q->where('branch_id',           $request->branch_id))
            ->when($request->vendor_id,    fn($q) => $q->where('outsource_vendor_id',  $request->vendor_id))
            ->orderBy('order_date')
            ->get()
            ->map(fn($o) => (object)[
                'order_id'        => $o->id,
                'production_no'   => $o->production_no,
                'order_date'      => $o->order_date,
                'expected_date'   => $o->expected_date,
                'vendor'          => optional($o->vendor)->name ?? '—',
                'branch'          => optional($o->branch)->name ?? '—',
                'status'          => $o->status,
                'total_raw_cost'  => (float)$o->total_raw_cost,
                'outsource_amount'=> (float)$o->outsource_amount,
                'total_cost'      => (float)$o->total_raw_cost + (float)$o->outsource_amount,
                'remarks'         => $o->remarks,
            ]);
    }

    // ── TAB 5: Summary / KPIs ─────────────────────────────────────────────
    private function summary($from, $to, $request)
    {
        $q = ProductionOrder::whereBetween('order_date', [$from, $to])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->whereNull('deleted_at');

        $orders     = $q->get();
        $receipts   = DB::table('production_receipts as pr')
            ->join('production_orders as po','pr.production_order_id','=','po.id')
            ->whereBetween('pr.receipt_date', [$from, $to])
            ->whereNull('po.deleted_at')
            ->when($request->branch_id, fn($q) => $q->where('po.branch_id', $request->branch_id))
            ->select('pr.*')->get();

        $receiptItems = DB::table('production_receipt_items as pri')
            ->join('production_receipts as pr','pri.receipt_id','=','pr.id')
            ->join('production_orders as po','pr.production_order_id','=','po.id')
            ->whereBetween('pr.receipt_date',[$from,$to])
            ->whereNull('po.deleted_at')
            ->when($request->branch_id, fn($q) => $q->where('po.branch_id', $request->branch_id))
            ->get();

        return (object)[
            'total_orders'       => $orders->count(),
            'draft'              => $orders->where('status','draft')->count(),
            'in_progress'        => $orders->where('status','in_progress')->count(),
            'completed'          => $orders->where('status','completed')->count(),
            'cancelled'          => $orders->where('status','cancelled')->count(),
            'inhouse'            => $orders->where('type','inhouse')->count(),
            'outsource'          => $orders->where('type','outsource')->count(),
            'total_raw_cost'     => $orders->sum('total_raw_cost'),
            'total_outsource'    => $orders->sum('outsource_amount'),
            'total_production_cost'=> $orders->sum(fn($o) => $o->total_raw_cost + $o->outsource_amount),
            'total_receipts'     => $receipts->count(),
            'total_received_qty' => $receiptItems->sum('quantity_received'),
            'total_defective_qty'=> $receiptItems->sum('quantity_defective'),
            'total_good_qty'     => $receiptItems->sum('quantity_received') - $receiptItems->sum('quantity_defective'),
            'defect_rate'        => $receiptItems->sum('quantity_received') > 0
                ? round($receiptItems->sum('quantity_defective') / $receiptItems->sum('quantity_received') * 100, 2)
                : 0,
        ];
    }
}