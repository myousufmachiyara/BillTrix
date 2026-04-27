<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{ChartOfAccounts, ProductVariation, StockBranchQuantity, StockMovement, PurchaseInvoice, PurchaseReturn, SaleInvoice, SaleReturn, Voucher, Branch, ProductionOrder};
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// ── Inventory Reports ─────────────────────────────────────────────────────────
class InventoryReportController extends Controller
{
    public function inventoryReports(Request $request)
    {
        $branchId   = $request->branch_id;
        $categoryId = $request->category_id;
        $search     = $request->search;

        $variations = ProductVariation::with('product.category','branchQuantities.branch')
            ->when($categoryId, fn($q) => $q->whereHas('product', fn($pq) => $pq->where('category_id',$categoryId)))
            ->when($search, fn($q) => $q->where('sku','like',"%{$search}%")->orWhereHas('product',fn($pq)=>$pq->where('name','like',"%{$search}%")))
            ->get()
            ->map(function ($v) use ($branchId) {
                $qty = $branchId
                    ? ($v->branchQuantities->where('branch_id',$branchId)->first()?->quantity ?? 0)
                    : $v->stock_quantity;
                return array_merge($v->toArray(), ['display_qty'=>$qty,'is_low'=>$qty<=$v->reorder_level&&$v->reorder_level>0]);
            });

        $branches   = Branch::all();
        $categories = \App\Models\ProductCategory::all();
        return view('reports.inventory.index', compact('variations','branches','categories'));
    }

    public function itemLedger(Request $request)
    {
        $variationId = $request->variation_id;
        $from        = $request->from ?? now()->startOfMonth()->toDateString();
        $to          = $request->to   ?? now()->toDateString();

        $variation = $variationId ? ProductVariation::with('product')->findOrFail($variationId) : null;
        $movements = $variationId ? StockMovement::where('variation_id',$variationId)->whereBetween('created_at',[$from.' 00:00:00',$to.' 23:59:59'])->with('branch')->get() : collect();
        $variations = ProductVariation::with('product')->get();
        $branches   = Branch::all();

        return view('reports.inventory.ledger', compact('variation','movements','variations','from','to'));
    }
}

// ── Purchase Reports ──────────────────────────────────────────────────────────
class PurchaseReportController extends Controller
{
    public function purchaseReports(Request $request)
    {
        $from     = $request->from ?? now()->startOfMonth()->toDateString();
        $to       = $request->to   ?? now()->toDateString();
        $vendorId = $request->vendor_id;

        $invoices = PurchaseInvoice::with('vendor','branch','items.product')
            ->whereBetween('invoice_date',[$from,$to])
            ->when($vendorId, fn($q)=>$q->where('vendor_id',$vendorId))
            ->when(auth()->user()->branch_id, fn($q)=>$q->where('branch_id',auth()->user()->branch_id))
            ->get();

        $returns  = PurchaseReturn::with('vendor','branch')
            ->whereBetween('return_date',[$from,$to])
            ->when($vendorId, fn($q)=>$q->where('vendor_id',$vendorId))
            ->get();

        $vendors  = ChartOfAccounts::vendors()->get();
        $summary  = $invoices->groupBy('vendor_id')->map(fn($g)=>['name'=>$g->first()->vendor->name,'total'=>$g->sum('net_amount'),'count'=>$g->count()]);

        return view('reports.purchase.index', compact('invoices','returns','vendors','summary','from','to'));
    }
}

// ── Sales Reports ─────────────────────────────────────────────────────────────
class SalesReportController extends Controller
{
    public function saleReports(Request $request)
    {
        $from       = $request->from ?? now()->startOfMonth()->toDateString();
        $to         = $request->to   ?? now()->toDateString();
        $customerId = $request->customer_id;
        $branchId   = $request->branch_id ?? auth()->user()->branch_id;

        $invoices = SaleInvoice::with('customer','branch','items.product.category','items.variation')
            ->whereBetween('invoice_date',[$from,$to])
            ->when($customerId,fn($q)=>$q->where('customer_id',$customerId))
            ->when($branchId,fn($q)=>$q->where('branch_id',$branchId))
            ->get();

        $customers = ChartOfAccounts::customers()->get();
        $branches  = Branch::all();

        // Item-wise summary
        $itemSummary = $invoices->flatMap->items->groupBy('variation_id')->map(function($items){
            $v = $items->first()->variation;
            return ['name'=>$v->product->name.' - '.($v->variation_name??''),'qty'=>$items->sum('quantity'),'revenue'=>$items->sum(fn($i)=>$i->quantity*$i->price),'cogs'=>$items->sum(fn($i)=>$i->quantity*$i->cost_price),'profit'=>$items->sum(fn($i)=>$i->quantity*($i->price-$i->cost_price))];
        });

        $totalRevenue = $invoices->sum('net_amount');
        $totalCOGS    = $invoices->flatMap->items->sum(fn($i)=>$i->quantity*$i->cost_price);
        $grossProfit  = $totalRevenue - $totalCOGS;

        return view('reports.sales.index', compact('invoices','customers','branches','itemSummary','totalRevenue','totalCOGS','grossProfit','from','to'));
    }
}

// ── Accounts Reports ──────────────────────────────────────────────────────────
class AccountsReportController extends Controller
{
    public function __construct(private AccountingService $accounting) {}

    public function accounts(Request $request)
    {
        $report = $request->report ?? 'trial_balance';
        $from   = $request->from ?? now()->startOfYear()->toDateString();
        $to     = $request->to   ?? now()->toDateString();

        $data = match($report) {
            'trial_balance'      => $this->trialBalance(),
            'profit_loss'        => $this->profitLoss($from,$to),
            'balance_sheet'      => $this->balanceSheet(),
            'cash_book'          => $this->bookFor('cash',$from,$to),
            'bank_book'          => $this->bookFor('bank',$from,$to),
            'account_ledger'     => $this->ledger($request->account_id,$from,$to),
            'receivables_aging'  => $this->aging('customer'),
            'payables_aging'     => $this->aging('vendor'),
            default              => [],
        };

        $accounts = ChartOfAccounts::where('is_active',1)->orderBy('name')->get();
        return view('reports.accounts.index', compact('report','data','accounts','from','to'));
    }

    private function trialBalance(): array
    {
        return ChartOfAccounts::with('subHead.head')->where('is_active',1)->get()->map(function($a){
            $dr = Voucher::where('ac_dr_sid',$a->id)->sum('amount');
            $cr = Voucher::where('ac_cr_sid',$a->id)->sum('amount');
            $bal= (float)$dr-(float)$cr+(float)$a->opening_balance;
            return ['account'=>$a,'dr'=>$dr,'cr'=>$cr,'balance'=>$bal];
        })->filter(fn($r)=>$r['dr']>0||$r['cr']>0||$r['balance']!=0)->values()->toArray();
    }

    private function profitLoss(string $from, string $to): array
    {
        $revenue  = ChartOfAccounts::where('account_type','revenue')->get()->sum(fn($a)=>max(0,$a->balance));
        $expenses = ChartOfAccounts::whereIn('account_type',['expenses','cogs'])->get()->sum(fn($a)=>max(0,$a->balance));
        return compact('revenue','expenses');
    }

    private function balanceSheet(): array
    {
        $assets      = ChartOfAccounts::whereIn('account_type',['cash','bank','inventory','asset','customer'])->get()->sum(fn($a)=>max(0,$a->balance));
        $liabilities = ChartOfAccounts::whereIn('account_type',['liability','vendor'])->get()->sum(fn($a)=>max(0,-$a->balance));
        $equity      = ChartOfAccounts::where('account_type','equity')->get()->sum(fn($a)=>$a->balance);
        return compact('assets','liabilities','equity');
    }

    private function bookFor(string $type, string $from, string $to): array
    {
        $account = ChartOfAccounts::where('account_type',$type)->first();
        return $account ? $this->accounting->getAccountLedger($account->id,$from,$to) : [];
    }

    private function ledger(?int $accountId, string $from, string $to): array
    {
        return $accountId ? $this->accounting->getAccountLedger($accountId,$from,$to) : [];
    }

    private function aging(string $type): array
    {
        $today   = now()->toDateString();
        $buckets = ['0-30','31-60','61-90','90+'];
        return ChartOfAccounts::where('account_type',$type)->get()->map(function($a) use ($today,$buckets){
            $bal = $a->balance;
            return ['account'=>$a,'balance'=>$bal,'aging'=>$buckets];
        })->filter(fn($r)=>abs($r['balance'])>0)->values()->toArray();
    }
}

// ── Production Reports ────────────────────────────────────────────────────────
class ProductionReportController extends Controller
{
    public function productionReports(Request $request)
    {
        $from   = $request->from ?? now()->startOfMonth()->toDateString();
        $to     = $request->to   ?? now()->toDateString();
        $orders = ProductionOrder::with('rawMaterials.variation.product','receipts.items.variation.product','branch')
            ->whereBetween('order_date',[$from,$to])->get();
        return view('reports.production.index', compact('orders','from','to'));
    }
}
