<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{Product, ProductVariation, Branch, ProductCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $tab    = $request->get('tab', 'SH');          // SH = Stock In Hand, IL = Item Ledger
        $itemId = $request->get('item_id');
        $from   = $request->get('from_date', date('Y-m-01'));
        $to     = $request->get('to_date',   date('Y-m-d'));

        $products   = Product::orderBy('name')->get();
        $branches   = Branch::all();
        $categories = ProductCategory::orderBy('name')->get();

        $itemLedger  = collect();
        $openingQty  = 0;
        $stockInHand = collect();

        // ── TAB 1: ITEM LEDGER ───────────────────────────────────────────
        if ($tab === 'IL' && $itemId) {

            // Opening balance (before $from)
            $opIn  = (float) DB::table('purchase_invoice_items as pii')
                ->join('purchase_invoices as pi','pii.purchase_invoice_id','=','pi.id')
                ->where('pii.item_id', $itemId)->whereNull('pi.deleted_at')
                ->where('pi.invoice_date','<',$from)->sum('pii.quantity');

            $opOut = (float) DB::table('sale_invoice_items as sii')
                ->join('sale_invoices as si','sii.sale_invoice_id','=','si.id')
                ->where('sii.item_id', $itemId)->whereNull('si.deleted_at')
                ->where('si.invoice_date','<',$from)->sum('sii.quantity');

            $opPRet = (float) DB::table('purchase_return_items as pri')
                ->join('purchase_returns as pr','pri.purchase_return_id','=','pr.id')
                ->where('pri.variation_id', null)->orWhere(function($q) use ($itemId) {
                    // match by item via variation lookup
                })->sum('pri.quantity');
            // Simplified: use stock_movements if available, else just purchases - sales
            $openingQty = $opIn - $opOut;

            // Period transactions
            $purchases = DB::table('purchase_invoice_items as pii')
                ->join('purchase_invoices as pi','pii.purchase_invoice_id','=','pi.id')
                ->select(
                    'pi.invoice_date as date',
                    DB::raw("'Purchase' as type"),
                    DB::raw("CONCAT('PUR-', pi.invoice_no) as description"),
                    'pii.quantity as qty_in',
                    DB::raw('0 as qty_out'),
                    'pii.price as unit_price'
                )
                ->where('pii.item_id', $itemId)->whereNull('pi.deleted_at')
                ->whereBetween('pi.invoice_date', [$from, $to]);

            $sales = DB::table('sale_invoice_items as sii')
                ->join('sale_invoices as si','sii.sale_invoice_id','=','si.id')
                ->select(
                    'si.invoice_date as date',
                    DB::raw("'Sale' as type"),
                    DB::raw("CONCAT('SI-', si.invoice_no) as description"),
                    DB::raw('0 as qty_in'),
                    'sii.quantity as qty_out',
                    'sii.price as unit_price'
                )
                ->where('sii.item_id', $itemId)->whereNull('si.deleted_at')
                ->whereBetween('si.invoice_date', [$from, $to]);

            $saleReturns = DB::table('sale_return_items as sri')
                ->join('sale_returns as sr','sri.sale_return_id','=','sr.id')
                ->join('product_variations as pv','sri.variation_id','=','pv.id')
                ->select(
                    'sr.return_date as date',
                    DB::raw("'Sale Return' as type"),
                    DB::raw("CONCAT('SRN-', sr.return_no) as description"),
                    'sri.quantity as qty_in',
                    DB::raw('0 as qty_out'),
                    'sri.price as unit_price'
                )
                ->where('pv.product_id', $itemId)
                ->whereBetween('sr.return_date', [$from, $to]);

            $purchaseReturns = DB::table('purchase_return_items as pri')
                ->join('purchase_returns as pr','pri.purchase_return_id','=','pr.id')
                ->join('product_variations as pv','pri.variation_id','=','pv.id')
                ->select(
                    'pr.return_date as date',
                    DB::raw("'Purchase Return' as type"),
                    DB::raw("CONCAT('PRN-', pr.return_no) as description"),
                    DB::raw('0 as qty_in'),
                    'pri.quantity as qty_out',
                    'pri.price as unit_price'
                )
                ->where('pv.product_id', $itemId)
                ->whereBetween('pr.return_date', [$from, $to]);

            $itemLedger = $purchases->union($sales)->union($saleReturns)->union($purchaseReturns)
                ->orderBy('date','asc')->get()
                ->map(fn($row) => (array) $row);
        }

        // ── TAB 2: STOCK IN HAND ─────────────────────────────────────────
        if ($tab === 'SH') {
            $variationQuery = ProductVariation::with('product')
                ->where('is_active', 1)
                ->when($itemId, fn($q) => $q->where('product_id', $itemId))
                ->orderBy('product_id')->orderBy('sku');

            foreach ($variationQuery->get() as $v) {
                $branchId = $request->branch_id ?? auth()->user()->branch_id;

                $stock = $branchId
                    ? \App\Models\StockBranchQuantity::where('variation_id',$v->id)->where('branch_id',$branchId)->value('quantity') ?? 0
                    : \App\Models\StockBranchQuantity::where('variation_id',$v->id)->sum('quantity');

                // Fallback to stock_quantity on variation itself
                if ($stock == 0) $stock = $v->stock_quantity;

                $stockInHand->push([
                    'variation_id'  => $v->id,
                    'product_id'    => $v->product_id,
                    'product'       => optional($v->product)->name ?? '—',
                    'variation'     => $v->variation_name ?: $v->sku,
                    'sku'           => $v->sku,
                    'quantity'      => (float) $stock,
                    'reorder_level' => (float) $v->reorder_level,
                    'cost_price'    => (float) $v->cost_price,
                    'sale_price'    => (float) $v->sale_price,
                    'stock_value'   => (float) $stock * (float) $v->cost_price,
                ]);
            }
        }

        return view('reports.inventory.index', compact(
            'products','branches','categories',
            'itemLedger','openingQty','stockInHand',
            'tab','from','to','itemId'
        ));
    }

    public function ledger(Request $request)
    {
        // Redirect to index with IL tab
        return redirect()->route('reports.inventory', array_merge($request->all(), ['tab' => 'IL']));
    }
}