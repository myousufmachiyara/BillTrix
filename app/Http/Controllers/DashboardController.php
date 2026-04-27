<?php
namespace App\Http\Controllers;

use App\Models\{SaleInvoice, PurchaseInvoice, ChartOfAccounts, ProductVariation, PostDatedCheque, SaleOrder, ProductionOrder};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todaySales  = SaleInvoice::whereDate('invoice_date', $today)->sum('net_amount');
        $monthSales  = SaleInvoice::whereDate('invoice_date', '>=', $monthStart)->sum('net_amount');

        $receivables = ChartOfAccounts::where('account_type', 'customer')
            ->get()->sum(fn($a) => max(0, $a->current_balance ?? 0));
        $payables    = ChartOfAccounts::where('account_type', 'vendor')
            ->get()->sum(fn($a) => max(0, -($a->current_balance ?? 0)));

        $lowStock        = ProductVariation::whereColumn('stock_quantity', '<=', 'reorder_level')
            ->where('reorder_level', '>', 0)->count();
        $openProduction  = ProductionOrder::whereIn('status', ['draft', 'in_progress'])->count();

        // PDC maturing within 7 days — $pendingPDC is what the view expects
        $pendingPDC = PostDatedCheque::with('account')
            ->where('status', 'pending')
            ->where('cheque_date', '<=', now()->addDays(7))
            ->orderBy('cheque_date')
            ->limit(10)
            ->get();

        // Chart data — last 6 months
        $months    = collect(range(5, 0))->map(fn($i) => now()->startOfMonth()->subMonths($i));
        $labels    = $months->map(fn($m) => $m->format('M Y'));

        $salesByMonth = SaleInvoice::select(
                DB::raw('YEAR(invoice_date) y'), DB::raw('MONTH(invoice_date) m'), DB::raw('SUM(net_amount) t')
            )->where('invoice_date', '>=', $months->first())
            ->groupBy('y', 'm')->get()->keyBy(fn($r) => $r->y.'-'.$r->m);

        $purchByMonth = PurchaseInvoice::select(
                DB::raw('YEAR(invoice_date) y'), DB::raw('MONTH(invoice_date) m'), DB::raw('SUM(net_amount) t')
            )->where('invoice_date', '>=', $months->first())
            ->groupBy('y', 'm')->get()->keyBy(fn($r) => $r->y.'-'.$r->m);

        $chartData = [
            'labels'    => $labels->values(),
            'sales'     => $months->map(fn($m) => (float)($salesByMonth[$m->year.'-'.$m->month]->t ?? 0))->values(),
            'purchases' => $months->map(fn($m) => (float)($purchByMonth[$m->year.'-'.$m->month]->t ?? 0))->values(),
        ];

        $recentSales  = SaleInvoice::with('customer')->latest()->limit(10)->get();

        return view('dashboard.index', compact(
            'todaySales', 'monthSales', 'receivables', 'payables',
            'lowStock', 'openProduction', 'pendingPDC', 'chartData', 'recentSales'
        ));
    }
}